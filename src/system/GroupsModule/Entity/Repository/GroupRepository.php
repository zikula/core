<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class GroupRepository extends EntityRepository implements GroupRepositoryInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns amount of groups.
     *
     * @param int $groupType     Optional type filter
     * @param int $excludedState Optional state exclusion filter
     *
     * @return integer
     */
    public function countGroups($groupType = null, $excludedState = null)
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('COUNT(tbl.gid)');

        if (null !== $groupType) {
            $qb->where('g.gtype = :gtype')
               ->setParameter('gtype', $groupType);
        }

        if (null !== $excludedState) {
            $qb->andWhere('g.state != :state')
               ->setParameter('state', $excludedState);
        }

        $query = $qb->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    /**
     * Returns groups for given arguments.
     *
     * @param array   $filters    Optional array with filters
     * @param array   $exclusions Optional array with exclusion filters
     * @param array   $sorting    Optional array with sorting criteria
     * @param integer $limit      Optional limitation for amount of retrieved objects
     * @param integer $offset     Optional start offset of retrieved objects
     *
     * @return array
     */
    public function getGroups($filters = [], $exclusions = [], $sorting = [], $limit = 0, $offset = 0)
    {
        $qb = $this->createQueryBuilder('tbl');

        // add clauses for where
        if (count($filters) > 0) {
            $i = 1;
            foreach ($filters as $w_key => $w_value) {
                $qb->andWhere($qb->expr()->eq('tbl.' . $w_key, '?' . $i))
                   ->setParameter($i, $w_value);
                $i++;
            }
        }
        if (count($exclusions) > 0) {
            $i = 1;
            foreach ($exclusions as $w_key => $w_value) {
                $qb->andWhere($qb->expr()->neq('tbl.' . $w_key, '?' . $i))
                   ->setParameter($i, $w_value);
                $i++;
            }
        }

        // add clause for ordering
        if (count($sorting) > 0) {
            foreach ($sorting as $sort => $sortdir) {
                $qb->addOrderBy('tbl.' . $sort, $sortdir);
            }
        }

        // add limit and offset
        if ($limit > 0) {
            $qb->setMaxResults($limit);
            if ($offset > 0) {
                $qb->setFirstResult($offset);
            }
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * @param string $indexField
     * @return array
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findAllAndIndexBy($indexField)
    {
        return $this->createQueryBuilder('g')
            ->indexBy('g', 'g.' . $indexField)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param bool $includeAll
     * @param bool $includeUnregistered
     * @return array
     */
    public function getGroupNamesById($includeAll = true, $includeUnregistered = true)
    {
        $groups = [];
        $groups[PermissionApi::ALL_GROUPS] = $this->translator->__('All groups');
        $groups[PermissionApi::UNREGISTERED_USER_GROUP] = $this->translator->__('Unregistered');

        $entities = parent::findAll();
        foreach ($entities as $group) {
            $groups[$group->getGid()] = $group->getName();
        }

        return $groups;
    }

    /**
     * @param string $name
     * @param int    $excludedGroupId
     * @return array
     */
    public function getGroupByName($name = '', $excludedGroupId = 0)
    {
        if ($name == '') {
            return null;
        }

        $qb = $this->createQueryBuilder('g');
        $qb->where($qb->expr()->eq('g.name', ':gname'))
            ->setParameter('gname', $name);

        // Optional, used when modifying a group to check if there is
        // already another group by that name.
        if (is_numeric($excludedGroupId) && $excludedGroupId > 0) {
            $qb->andWhere($qb->expr()->neq('g.gid', ':ggid'))
               ->setParameter('ggid', $excludedGroupId);
        }

        $query = $qb->getQuery();

        // execute query
        return $query->getOneOrNullResult();
    }
}

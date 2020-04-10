<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class GroupRepository extends ServiceEntityRepository implements GroupRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupEntity::class);
    }

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function countGroups(int $groupType = null, int $excludedState = null): int
    {
        $qb = $this->createQueryBuilder('g')
            ->select('COUNT(g.gid)');

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

    public function getGroups(
        array $filters = [],
        array $exclusions = [],
        array $sorting = [],
        int $page = 1,
        int $pageSize = 25
    ): PaginatorInterface {
        $qb = $this->createQueryBuilder('g');

        // add clauses for where
        if (count($filters) > 0) {
            $i = 1;
            foreach ($filters as $w_key => $w_value) {
                if (is_array($w_value)) {
                    $qb->andWhere($qb->expr()->in('g.' . $w_key, '?' . $i));
                } else {
                    $qb->andWhere($qb->expr()->eq('g.' . $w_key, '?' . $i));
                }
                $qb->setParameter($i, $w_value);
                $i++;
            }
        }
        if (count($exclusions) > 0) {
            $i = 1;
            foreach ($exclusions as $w_key => $w_value) {
                if (is_array($w_value)) {
                    $qb->andWhere($qb->expr()->notIn('g.' . $w_key, '?' . $i));
                } else {
                    $qb->andWhere($qb->expr()->neq('g.' . $w_key, '?' . $i));
                }
                $qb->setParameter($i, $w_value);
                $i++;
            }
        }

        // add clause for ordering
        if (count($sorting) > 0) {
            foreach ($sorting as $sort => $sortdir) {
                $qb->addOrderBy('g.' . $sort, $sortdir);
            }
        }

        return (new Paginator($qb, $pageSize))->paginate($page);
    }

    public function findAllAndIndexBy(string $indexField): array
    {
        return $this->createQueryBuilder('g')
            ->indexBy('g', 'g.' . $indexField)
            ->getQuery()
            ->getResult();
    }

    public function getGroupNamesById(bool $includeAll = true, bool $includeUnregistered = true): array
    {
        $groups = [];
        $groups[PermissionApi::ALL_GROUPS] = $this->translator->trans('All groups');
        $groups[PermissionApi::UNREGISTERED_USER_GROUP] = $this->translator->trans('Unregistered');

        $entities = $this->findAll();
        foreach ($entities as $group) {
            $groups[$group->getGid()] = $group->getName();
        }

        return $groups;
    }

    public function getGroupByName(string $name = '', int $excludedGroupId = 0): array
    {
        if ('' === $name) {
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

        return $query->getOneOrNullResult();
    }
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\PermissionsModule\Entity\PermissionEntity;
use Zikula\PermissionsModule\Entity\RepositoryInterface\PermissionRepositoryInterface;

class PermissionRepository extends EntityRepository implements PermissionRepositoryInterface
{
    public function getPermissionsByGroups(array $groups)
    {
        $qb = $this->createQueryBuilder('p');
        $query = $qb->select('p')
            ->where($qb->expr()->in('p.gid', ':groups'))
            ->setParameter('groups', $groups)
            ->orderBy('p.sequence', 'ASC')
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * Optionally filter a selection of PermissionEntity by group or component or both
     * @param int $group
     * @param null $component
     * @return array
     */
    public function getFilteredPermissions($group = PermissionApi::ALL_GROUPS, $component = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->orderBy('p.sequence', 'ASC');
        if (PermissionApi::ALL_GROUPS != $group) {
            $qb->where('p.gid = :gid')
                ->setParameter('gid', $group);
        }
        if (isset($component)) {
            $qb->andWhere("p.component LIKE :permgrpparts")
                ->setParameter('permgrpparts', $component.'%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getAllComponents()
    {
        $all = parent::findBy([], ['sequence' => 'ASC']);
        $components = [];
        foreach ($all as $perm) {
            // extract components, we keep everything up to the first colon
            $parts = explode(':', $perm->getComponent());
            $components[$parts[0]] = $parts[0];
        }

        return $components;
    }

    public function persistAndFlush(PermissionEntity $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    /**
     * Get the highest sequential number
     * @return int
     */
    public function getMaxSequence()
    {
        $qb = $this->createQueryBuilder('p');
        $query = $qb->select($qb->expr()->max('p.sequence'))
            ->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    /**
     * Update all sequence values >= the provided $value by the provided $amount
     *   to increment, amount = 1; to decrement, amount = -1
     * @param $value
     * @param int $amount
     */
    public function updateSequencesFrom($value, $amount = 1)
    {
        $query = $this->_em->createQueryBuilder()
            ->update('ZikulaPermissionsModule:PermissionEntity', 'p')
            ->set('p.sequence', 'p.sequence + :amount')
            ->where('p.sequence >= :value')
            ->setParameter('amount', $amount)
            ->setParameter('value', $value)
            ->getQuery();
        $query->execute();
    }

    /**
     * ReSequence all perms
     */
    public function reSequence()
    {
        /** @var PermissionEntity[] $permissions */
        $permissions = parent::findBy([], ['sequence' => 'ASC']);
        $sequence = 1;
        foreach ($permissions as $permission) {
            $permission->setSequence($sequence);
            $sequence++;
        }
        $this->_em->flush();
    }

    /**
     * Deletes all permissions for a given group.
     *
     * @param int $groupId The group id
     */
    public function deleteGroupPermissions($groupId = 0)
    {
        if ($groupId < 1) {
            return;
        }

        $qb = $this->_em->createQueryBuilder('p')
            ->delete('ZikulaPermissionsModule:PermissionEntity', 'p')
            ->where('p.gid = :gid')
            ->setParameter('gid', $groupId);

        $query = $qb->getQuery();

        $query->execute();
    }
}

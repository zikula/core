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
        if ($group != -1) {
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
}

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
use Zikula\PermissionsModule\Entity\PermissionEntity;
use Zikula\PermissionsModule\Entity\RepositoryInterface\PermissionRepositoryInterface;

class PermissionRepository extends EntityRepository implements PermissionRepositoryInterface
{
    public function getPermissionsByGroups(array $groups)
    {
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('p')
            ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
            ->where($qb->expr()->in('p.gid', ':groups'))
            ->setParameter('groups', $groups)
            ->orderBy('p.sequence', 'ASC')
            ->getQuery();

        return $query->getArrayResult();
    }
}

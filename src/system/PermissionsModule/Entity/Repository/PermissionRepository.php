<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
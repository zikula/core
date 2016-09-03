<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Entity\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;

class AdminModuleRepository extends EntityRepository implements ObjectRepository, Selectable
{
    public function persistAndFlush($entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    public function countModulesByCategory($cid)
    {
        $query = $this->createQueryBuilder('m')
            ->select('count(m.amid)')
            ->where('m.cid = :cid')
            ->setParameter('cid', $cid)
            ->getQuery();

        return (int)$query->getSingleScalarResult();
    }
}

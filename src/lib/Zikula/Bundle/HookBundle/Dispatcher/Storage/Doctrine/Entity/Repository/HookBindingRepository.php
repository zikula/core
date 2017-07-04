<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookBindingRepositoryInterface;

class HookBindingRepository extends EntityRepository implements HookBindingRepositoryInterface
{
    public function deleteByBothAreas($subscriberArea, $providerArea)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(HookBindingEntity::class, 't')
            ->where('t.pareaid = ?1 AND t.sareaid = ?2')
            ->setParameters([1 => $providerArea, 2 => $subscriberArea])
            ->getQuery()
            ->execute();
    }

    public function selectByAreaName($areaName, $type = 'sareaid')
    {
        $type = in_array($type, ['sareaid', 'pareaid']) ? $type : 'sareaid';
        $order = new OrderBy();
        $order->add('t.sortorder', 'ASC');
        $order->add('t.sareaid', 'ASC');

        return $this->createQueryBuilder('t')
            ->where("t.$type = ?1")
            ->orderBy($order)
            ->setParameter(1, $areaName)
            ->getQuery()
            ->getArrayResult();
    }

    public function setSortOrder($order, $subscriberAreaName, $providerAreaName)
    {
        $this->_em->createQueryBuilder()
            ->update(HookBindingEntity::class, 't')
            ->set('t.sortorder', $order)
            ->where('t.sareaid = ?1 AND t.pareaid = ?2')
            ->setParameters([1 => $subscriberAreaName, 2 => $providerAreaName])
            ->getQuery()
            ->execute();
    }

    public function findOneOrNullByAreas($subscriberArea, $providerArea)
    {
        return $this->createQueryBuilder('t')
            ->where('t.sareaid = ?1 AND t.pareaid = ?2')
            ->setParameters([1 => $subscriberArea, 2 => $providerArea])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByOwners($subscriberOwner, $providerOwner)
    {
        return $this->createQueryBuilder('t')
            ->where('t.sowner = ?1 AND t.powner = ?2')
            ->setParameters([1 => $subscriberOwner, 2 => $providerOwner])
            ->getQuery()
            ->getArrayResult();
    }

    public function deleteAllByOwner($owner)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(HookBindingEntity::class, 't')
            ->where('t.sowner = ?1 OR t.powner = ?2')
            ->setParameters([1 => $owner, 2 => $owner])
            ->getQuery()
            ->execute();
    }
}

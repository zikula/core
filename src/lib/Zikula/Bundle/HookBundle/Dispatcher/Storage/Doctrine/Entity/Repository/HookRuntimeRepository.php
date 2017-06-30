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
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookRuntimeRepositoryInterface;

class HookRuntimeRepository extends EntityRepository implements HookRuntimeRepositoryInterface
{
    public function truncate()
    {
        $this->_em->createQueryBuilder()
            ->delete(HookRuntimeEntity::class)
            ->getQuery()
            ->execute();
    }

    public function getOneOrNullByEventName($eventName)
    {
        return $this->createQueryBuilder('t')
            ->where('t.eventname = :name')
            ->setParameter('name', $eventName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteAllByOwner($owner)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(HookRuntimeEntity::class, 't')
            ->where('t.sowner = ?1 OR t.powner = ?2')
            ->setParameters([1 => $owner, 2 => $owner])
            ->getQuery()
            ->execute();
    }
}

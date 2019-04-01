<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookRuntimeRepositoryInterface;

class HookRuntimeRepository extends ServiceEntityRepository implements HookRuntimeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HookRuntimeEntity::class);
    }

    public function truncate(): void
    {
        $this->_em->createQueryBuilder()
            ->delete(HookRuntimeEntity::class)
            ->getQuery()
            ->execute();
    }

    public function getOneOrNullByEventName(string $eventName)
    {
        $result = $this->createQueryBuilder('t')
            ->where('t.eventname = :name')
            ->setParameter('name', $eventName)
            ->getQuery()
            ->getResult();

        return count($result) > 0 ? array_shift($result) : $result;
    }

    public function deleteAllByOwner(string $owner): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(HookRuntimeEntity::class, 't')
            ->where('t.sowner = ?1 OR t.powner = ?2')
            ->setParameters([1 => $owner, 2 => $owner])
            ->getQuery()
            ->execute();
    }
}

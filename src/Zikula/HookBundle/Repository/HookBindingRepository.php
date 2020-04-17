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

namespace Zikula\Bundle\HookBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\Bundle\HookBundle\Entity\HookBindingEntity;
use Zikula\Bundle\HookBundle\RepositoryInterface\HookBindingRepositoryInterface;

class HookBindingRepository extends ServiceEntityRepository implements HookBindingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HookBindingEntity::class);
    }

    public function deleteByBothAreas(string $subscriberArea, string $providerArea): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(HookBindingEntity::class, 't')
            ->where('t.pareaid = ?1 AND t.sareaid = ?2')
            ->setParameters([1 => $providerArea, 2 => $subscriberArea])
            ->getQuery()
            ->execute();
    }

    public function selectByAreaName(string $areaName, string $type = 'sareaid'): array
    {
        $type = in_array($type, ['sareaid', 'pareaid']) ? $type : 'sareaid';
        $order = new OrderBy();
        $order->add('t.sortorder', 'ASC');
        $order->add('t.sareaid', 'ASC');

        return $this->createQueryBuilder('t')
            ->where("t.${type} = ?1")
            ->orderBy($order)
            ->setParameter(1, $areaName)
            ->getQuery()
            ->getArrayResult();
    }

    public function setSortOrder(int $order, string $subscriberAreaName, string $providerAreaName): void
    {
        $this->_em->createQueryBuilder()
            ->update(HookBindingEntity::class, 't')
            ->set('t.sortorder', $order)
            ->where('t.sareaid = ?1 AND t.pareaid = ?2')
            ->setParameters([1 => $subscriberAreaName, 2 => $providerAreaName])
            ->getQuery()
            ->execute();
    }

    public function findOneOrNullByAreas(string $subscriberArea, string $providerArea)
    {
        return $this->createQueryBuilder('t')
            ->where('t.sareaid = ?1 AND t.pareaid = ?2')
            ->setParameters([1 => $subscriberArea, 2 => $providerArea])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByOwners(string $subscriberOwner, string $providerOwner): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.sowner = ?1 AND t.powner = ?2')
            ->setParameters([1 => $subscriberOwner, 2 => $providerOwner])
            ->getQuery()
            ->getArrayResult();
    }

    public function deleteAllByOwner(string $owner): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(HookBindingEntity::class, 't')
            ->where('t.sowner = ?1 OR t.powner = ?2')
            ->setParameters([1 => $owner, 2 => $owner])
            ->getQuery()
            ->execute();
    }
}

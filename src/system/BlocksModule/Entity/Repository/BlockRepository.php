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

namespace Zikula\BlocksModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;

class BlockRepository extends ServiceEntityRepository implements BlockRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlockEntity::class);
    }

    public function getFilteredBlocks(array $filters = [])
    {
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('b')
            ->from($this->_entityName, 'b')
        ;
        if (isset($filters['position'])) {
            $subQb = $this->_em->createQueryBuilder();
            $query
                ->join('b.placements', 'p')
                ->where($qb->expr()->in('p.position',
                    $subQb->select('bp')
                        ->from('ZikulaBlocksModule:BlockPositionEntity', 'bp')
                        ->where('bp.pid = ?1')
                        ->getDQL()
                ))
                ->setParameter(1, $filters['position'])
            ;
            unset($filters['position']);
        }
        $paramIndex = 2;
        $sortField = $filters['sort-field'] ?? 'bid';
        $sortDirection = $filters['sort-direction'] ?? 'ASC';
        unset($filters['sort-field'], $filters['sort-direction']);
        foreach ($filters as $key => $value) {
            if (!isset($value)) {
                unset($filters[$key]);
            } else {
                $query->andWhere($qb->expr()->eq('b.' . $key, '?' . $paramIndex))
                    ->setParameter($paramIndex, $value);
            }
        }
        $query->orderBy('b.' . $sortField, $sortDirection);

        return $query->getQuery()->getResult();
    }

    public function persistAndFlush(BlockEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }
}

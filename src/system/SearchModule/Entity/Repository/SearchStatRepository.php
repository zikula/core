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

namespace Zikula\SearchModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Entity\SearchStatEntity;

class SearchStatRepository extends ServiceEntityRepository implements SearchStatRepositoryInterface
{
    use RepositoryGetResultsTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchStatEntity::class);
    }

    public function countStats(): int
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('COUNT(tbl.id)');

        $query = $qb->getQuery();

        return (int) $query->getSingleScalarResult();
    }

    public function getStats(array $filters = [], array $sorting = [], int $page = 1, int $pageSize = 25): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('tbl');

        return $this->doGetPaginatedResults($qb, $filters, $sorting, $page, $pageSize);
    }

    public function persistAndFlush(SearchStatEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }
}

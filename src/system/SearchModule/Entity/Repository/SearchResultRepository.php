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
use Zikula\SearchModule\Entity\RepositoryInterface\SearchResultRepositoryInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;

class SearchResultRepository extends ServiceEntityRepository implements SearchResultRepositoryInterface
{
    use RepositoryGetResultsTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchResultEntity::class);
    }

    public function getResults(array $filters = [], array $sorting = [], int $page = 1, int $pageSize = 25): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('tbl');

        return $this->doGetPaginatedResults($qb, $filters, $sorting, $page, $pageSize);
    }

    public function clearOldResults(string $sessionId = ''): void
    {
        $qb = $this->_em->createQueryBuilder()
            ->delete(SearchResultEntity::class, 'tbl')
            ->where('DATE_ADD(tbl.found, 1, \'DAY\') < CURRENT_TIMESTAMP()');

        if ('' !== $sessionId) {
            $qb->orWhere('tbl.sesid = :sid')
               ->setParameter('sid', $sessionId);
        }

        $query = $qb->getQuery();

        $query->execute();
    }

    public function persist(SearchResultEntity $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(SearchResultEntity $entity = null): void
    {
        $this->_em->flush();
    }

    public function truncateTable(): void
    {
        $qb = $this->_em->createQueryBuilder()
            ->delete(SearchResultEntity::class, 'tbl');
        $query = $qb->getQuery();

        $query->execute();
    }
}

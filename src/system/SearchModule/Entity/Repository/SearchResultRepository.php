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

namespace Zikula\SearchModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchResultRepositoryInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;

/**
 * Repository class used to implement own convenience methods for performing certain DQL queries.
 *
 * This is the repository class for search results.
 */
class SearchResultRepository extends ServiceEntityRepository implements SearchResultRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchResultEntity::class);
    }

    public function countResults(string $sessionId = ''): int
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('COUNT(tbl.sesid)');

        if ('' !== $sessionId) {
            $qb->where('tbl.sesid = :sid')
               ->setParameter('sid', $sessionId);
        }

        $query = $qb->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    public function getResults(array $filters = [], array $sorting = [], int $limit = 0, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('tbl');

        // add clauses for where
        if (count($filters) > 0) {
            $i = 1;
            foreach ($filters as $w_key => $w_value) {
                $qb->andWhere($qb->expr()->eq('tbl.' . $w_key, '?' . $i))
                   ->setParameter($i, $w_value);
                $i++;
            }
        }

        // add clause for ordering
        if (count($sorting) > 0) {
            foreach ($sorting as $sort => $sortdir) {
                $qb->addOrderBy('tbl.' . $sort, $sortdir);
            }
        }

        // add limit and offset
        if ($limit > 0) {
            $qb->setMaxResults($limit);
            if ($offset > 0) {
                $qb->setFirstResult($offset);
            }
        }

        $query = $qb->getQuery();

        return $query->getArrayResult();
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

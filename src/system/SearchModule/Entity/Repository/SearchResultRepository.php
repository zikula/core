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
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
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

    public function getResults(array $filters = [], array $sorting = [], int $page = 1, int $pageSize = 25): PaginatorInterface
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

        return (new Paginator($qb, $pageSize))->paginate($page);
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

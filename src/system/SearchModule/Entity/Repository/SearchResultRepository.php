<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

/**
 * Repository class used to implement own convenience methods for performing certain DQL queries.
 *
 * This is the repository class for search results.
 */
class SearchResultRepository extends EntityRepository
{
    /**
     * Returns amount of results.
     *
     * @param string $sessionId Session id to filter results.
     *
     * @return integer
     */
    public function countResults($sessionId = '')
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('COUNT(tbl.sesid)');

        if ($sessionId != '') {
            $qb->where('s.sesid = :sid')
               ->setParameter('sid', $sessionId);
        }

        $query = $qb->getQuery();

        $count = (int)$query->getSingleScalarResult();

        return $count;
    }

    /**
     * Returns results for given arguments.
     *
     * @param array   $filters Optional array with filters
     * @param array   $sorting Optional array with sorting criteria
     * @param integer $limit   Optional limitation for amount of retrieved objects
     * @param integer $offset  Optional start offset of retrieved objects
     *
     * @return array
     */
    public function getResults($filters = [], $sorting = [], $limit = 0, $offset = 0)
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

    /**
     * Deletes all results for the current session.
     *
     * @param string $sessionId Session id of current user.
     *
     * @return void
     */
    public function clearOldResults($sessionId = '')
    {
        $qb = $this->_em->createQueryBuilder('tbl')
            ->delete('Zikula\SearchModule\Entity\SearchResultEntity', 'tbl')
            ->where('DATE_ADD(s.found, 1, \'DAY\') < CURRENT_TIMESTAMP()');

        if ($sessionId != '') {
            $qb->orWhere('tbl.sesid = :sid')
               ->setParameter('sid', $sessionId);
        }

        $query = $qb->getQuery();

        $query->execute();
    }
}

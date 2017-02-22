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
use Zikula\SearchModule\Entity\RepositoryInterface\SearchResultRepositoryInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;

/**
 * Repository class used to implement own convenience methods for performing certain DQL queries.
 *
 * This is the repository class for search results.
 */
class SearchResultRepository extends EntityRepository implements SearchResultRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function countResults($sessionId = '')
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('COUNT(tbl.sesid)');

        if ($sessionId != '') {
            $qb->where('tbl.sesid = :sid')
               ->setParameter('sid', $sessionId);
        }

        $query = $qb->getQuery();

        $count = (int)$query->getSingleScalarResult();

        return $count;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function clearOldResults($sessionId = '')
    {
        $qb = $this->_em->createQueryBuilder()
            ->delete('Zikula\SearchModule\Entity\SearchResultEntity', 'tbl')
            ->where('DATE_ADD(tbl.found, 1, \'DAY\') < CURRENT_TIMESTAMP()');

        if ($sessionId != '') {
            $qb->orWhere('tbl.sesid = :sid')
               ->setParameter('sid', $sessionId);
        }

        $query = $qb->getQuery();

        $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function persist(SearchResultEntity $entity)
    {
        $this->_em->persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(SearchResultEntity $entity = null)
    {
        $this->_em->flush($entity);
    }
}

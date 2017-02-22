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
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Entity\SearchStatEntity;

/**
 * Repository class used to implement own convenience methods for performing certain DQL queries.
 *
 * This is the repository class for search statistics.
 */
class SearchStatRepository extends EntityRepository implements SearchStatRepositoryInterface
{
    /**
* @inheritDoc
     */
    public function countStats()
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('COUNT(tbl.id)');

        $query = $qb->getQuery();

        $count = (int)$query->getSingleScalarResult();

        return $count;
    }

    /**
* @inheritDoc
     */
    public function getStats($filters = [], $sorting = [], $limit = 0, $offset = 0)
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

        return $query->getResult();
    }

    /**
* @inheritDoc
     */
    public function persistAndFlush(SearchStatEntity $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }
}

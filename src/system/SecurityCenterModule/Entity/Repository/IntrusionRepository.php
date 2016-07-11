<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

/**
 * Repository class used to implement own convenience methods for performing certain DQL queries.
 *
 * This is the repository class for intrusion entities.
 */
class IntrusionRepository extends EntityRepository
{
    /**
     * Returns amount of intrusions for given arguments.
     *
     * @param array $filters Optional array with filters.
     *
     * @return integer
     */
    public function countIntrusions($filters = [])
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('COUNT(tbl.id)');

        $qb = $this->addCommonFilters($qb, $filters);

        $query = $qb->getQuery();

        $count = (int)$query->getSingleScalarResult();

        return $count;
    }

    /**
     * Returns intrusions for given arguments.
     *
     * @param array   $filters Optional array with filters.
     * @param array   $sorting Optional array with sorting criteria.
     * @param integer $limit   Optional limitation for amount of retrieved objects.
     * @param integer $offset  Optional start offset of retrieved objects.
     *
     * @return array
     */
    public function getIntrusions($filters = [], $sorting = [], $limit = 0, $offset = 0)
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('tbl');

        $qb = $this->addCommonFilters($qb, $filters, $sorting);

        // add clause for ordering
        if (isset($sorting['username'])) {
            $sortdir = $sorting['username'];
            unset($sorting['username']);

            $qb->from('ZikulaUsersModule:UserEntity', 'u');
            $qb->andWhere($qb->expr()->eq('tbl.user', 'u.uid'));
            $qb->addOrderBy('u.uname', $sortdir);
        }

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
     * Adds common filters to the given query builder.
     *
     * @param QueryBuilder $qb      The current query builder instance.
     * @param array        $filters Optional array with filters.
     *
     * @return QueryBuilder The enriched query builder.
     */
    private function addCommonFilters(QueryBuilder $qb, $filters = [])
    {
        // add clause for user
        if (isset($filters['uid'])) {
            $uid = $filters['uid'];
            unset($filters['uid']);

            if ($uid > 0) {
                $qb->from('ZikulaUsersModule:UserEntity', 'u');
                $qb->andWhere($qb->expr()->eq('tbl.user', 'u.uid'));
                $qb->andWhere($qb->expr()->eq('tbl.user', ':uid'))
                   ->setParameter('uid', $uid);
            }
        }

        // add clauses for where
        if (count($filters) > 0) {
            $i = 1;
            foreach ($filters as $w_key => $w_value) {
                $qb->andWhere($qb->expr()->eq('tbl.' . $w_key, '?' . $i))
                   ->setParameter($i, $w_value);
                $i++;
            }
        }

        return $qb;
    }

    /**
     * Selects a list of distinct values for a given field.
     *
     * @param string $fieldName Name of field to select.
     *
     * @return array
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function getDistinctFieldValues($fieldName)
    {
        if (!in_array($fieldName, ['uid', 'name', 'tag', 'value', 'page', 'ip', 'impact'])) {
            throw new InvalidArgumentException(__('Invalid field name received for distinct values selection!'));
        }

        $qb = $this->createQueryBuilder('tbl');

        if ($fieldName == 'uid') {
            $qb->select('DISTINCT(u.' . $fieldName . ')')
               ->from('ZikulaUsersModule:UserEntity', 'u')
               ->where($qb->expr()->eq('tbl.user', 'u.uid'))
               ->addOrderBy('u.uname', 'ASC');
        } else {
            $qb->select('DISTINCT(tbl.' . $fieldName . ')')
               ->addOrderBy('tbl.' . $fieldName, 'ASC');
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * Helper method for truncating the table.
     *
     * @return void
     */
    public function truncateTable()
    {
        /*$connection = $this->getEntityManager()->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('sc_intrusion', true));*/

        $qb = $this->createQueryBuilder('tbl')
            ->delete('Zikula\RoutesModule\Entity\RouteEntity', 'tbl');
        $query = $qb->getQuery();

        $query->execute();
    }
}

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

namespace Zikula\SecurityCenterModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\SecurityCenterModule\Entity\IntrusionEntity;

/**
 * Repository class used to implement own convenience methods for performing certain DQL queries.
 *
 * This is the repository class for intrusion entities.
 */
class IntrusionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IntrusionEntity::class);
    }

    /**
     * Returns intrusions for given arguments.
     */
    public function getIntrusions(array $filters = [], array $sorting = [], int $page = 1, int $pageSize = 25): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('tbl')
            ->select('tbl');

        $qb = $this->addCommonFilters($qb, $filters);

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

        return (new Paginator($qb, $pageSize))->paginate($page);
    }

    /**
     * Adds common filters to the given query builder.
     */
    private function addCommonFilters(QueryBuilder $qb, array $filters = []): QueryBuilder
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
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function getDistinctFieldValues(string $fieldName): array
    {
        if (!in_array($fieldName, ['uid', 'name', 'tag', 'value', 'page', 'ip', 'impact'])) {
            throw new InvalidArgumentException('Invalid field name received for distinct values selection!');
        }

        $qb = $this->createQueryBuilder('tbl');

        if ('uid' === $fieldName) {
            $qb->select('DISTINCT(u.uname)')
               ->from('ZikulaUsersModule:UserEntity', 'u')
               ->where($qb->expr()->eq('tbl.user', 'u.uid'))
               ->addOrderBy('u.uname', 'ASC');
        } else {
            $qb->select('DISTINCT(tbl.' . $fieldName . ')')
               ->addOrderBy('tbl.' . $fieldName, 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Helper method for truncating the table.
     */
    public function truncateTable(): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete(IntrusionEntity::class, 'tbl');
        $query = $qb->getQuery();

        $query->execute();
    }
}

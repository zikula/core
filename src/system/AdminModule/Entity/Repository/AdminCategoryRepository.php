<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AdminCategoryRepository extends EntityRepository
{
    public function countCategories()
    {
        $query = $this->createQueryBuilder('c')
            ->select('COUNT(c.cid)')
            ->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    public function getModuleCategory($moduleId)
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.mid = :mid')
            ->setParameter('mid', $moduleId)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getIndexedCollection($indexBy)
    {
        $collection = $this->createQueryBuilder('c')
            ->indexBy('c', 'c.' . $indexBy)
            ->getQuery()
            ->getResult();

        return $collection;
    }

    public function getPagedCategories($orderBy = [], $offset = 0, $limit = 0)
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                $qb->addOrderBy('c.' . $sort, $order);
            }
        }

        if (!empty($limit) && !empty($offset)) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        return new Paginator($qb);
    }
}

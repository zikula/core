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
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminCategoryRepositoryInterface;

class AdminCategoryRepository extends EntityRepository implements AdminCategoryRepositoryInterface
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
        $query = $this->_em->createQueryBuilder('m')
            ->select('m.cid')
            ->from('ZikulaAdminModule:AdminModuleEntity', 'm')
            ->where('m.mid = :mid')
            ->setParameter('mid', $moduleId)
            ->getQuery();

        try {
            $categoryId = (int)$query->getSingleScalarResult();
        } catch (NoResultException $e) {
            return null;
        }
        if (!$categoryId) {
            return null;
        }

        $query = $this->createQueryBuilder('c')
            ->where('c.cid = :cid')
            ->setParameter('cid', $categoryId)
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

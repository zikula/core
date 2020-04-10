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

namespace Zikula\AdminModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminCategoryRepositoryInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;

class AdminCategoryRepository extends ServiceEntityRepository implements AdminCategoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminCategoryEntity::class);
    }

    public function countCategories(): int
    {
        $query = $this->createQueryBuilder('c')
            ->select('COUNT(c.cid)')
            ->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    public function getModuleCategory(int $moduleId): ?AdminCategoryEntity
    {
        $query = $this->_em->createQueryBuilder()
            ->select('m.cid')
            ->from('ZikulaAdminModule:AdminModuleEntity', 'm')
            ->where('m.mid = :mid')
            ->setParameter('mid', $moduleId)
            ->getQuery();

        $categoryId = (int)$query->getSingleScalarResult();
        if (!$categoryId) {
            return null;
        }

        $query = $this->createQueryBuilder('c')
            ->where('c.cid = :cid')
            ->setParameter('cid', $categoryId)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getIndexedCollection(string $indexBy)
    {
        $collection = $this->createQueryBuilder('c')
            ->indexBy('c', 'c.' . $indexBy)
            ->getQuery()
            ->getResult();

        return $collection;
    }

    public function getPagedCategories(array $orderBy = [], int $page = 1, int $pageSize = 25): PaginatorInterface
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                $qb->addOrderBy('c.' . $sort, $order);
            }
        }

        return (new Paginator($qb, $pageSize))->paginate($page);
    }
}

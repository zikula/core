<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRegistryRepositoryInterface;

class CategoryRegistryRepository extends ServiceEntityRepository implements CategoryRegistryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryRegistryEntity::class);
    }

    // nothing yet
}

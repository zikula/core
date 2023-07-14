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

namespace Zikula\CategoriesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Gedmo\Tree\RepositoryInterface;
use Zikula\CategoriesBundle\Entity\Category;

interface CategoryRepositoryInterface extends ObjectRepository, Selectable, RepositoryInterface, ServiceEntityRepositoryInterface
{
    /**
     * Returns amount of categories for specified filters.
     */
    public function countForContext(string $name = '', int $parentId = 0, int $excludedId = null): int;

    /**
     * Returns the last added category within a given parent category.
     */
    public function getLastByParent(int $parentId = 0): ?Category;
}

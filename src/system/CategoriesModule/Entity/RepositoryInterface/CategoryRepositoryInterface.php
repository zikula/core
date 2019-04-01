<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Gedmo\Tree\RepositoryInterface;
use Zikula\CategoriesModule\Entity\CategoryEntity;

interface CategoryRepositoryInterface extends ObjectRepository, Selectable, RepositoryInterface
{
    /**
     * Returns amount of categories for specified filters.
     */
    public function countForContext(string $name = '', int $parentId = 0, int $excludedId = 0): int;

    /**
     * Returns the last added category within a given parent category.
     */
    public function getLastByParent(int $parentId = 0): ?CategoryEntity;

    /**
     * Updates the parent id of one or multiple categories.
     *
     * @param integer $oldParentId The categoryID of the category to be updated
     * @param integer $newParentId The categoryID of the new parent category
     * @param boolean $includeRoot Whether or not to also move the root folder (optional) (default=true)
     */
    public function updateParent(int $oldParentId = 0, int $newParentId = 0, bool $includeRoot = true): void;
}

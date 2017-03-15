<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Gedmo\Tree\RepositoryInterface;

interface CategoryRepositoryInterface extends ObjectRepository, Selectable, RepositoryInterface
{
    /**
     * Returns amount of categories for specified filters.
     *
     * @param string $name       Name filter
     * @param int    $parentId   Optional parent category id filter
     * @param int    $excludedId Optional category id filter for exclusion
     *
     * @return integer
     */
    public function countForContext($name = '', $parentId = 0, $excludedId = 0);

    /**
     * Returns the last added category within a given parent category.
     *
     * @param int $parentId Parent category id
     *
     * @return CategoryEntity|null
     */
    public function getLastByParent($parentId = 0);

    /**
     * Updates the parent id of one or multiple categories.
     *
     * @param integer $oldParentId The categoryID of the category to be updated
     * @param integer $newParentId The categoryID of the new parent category
     * @param boolean $includeRoot Whether or not to also move the root folder (optional) (default=true)
     */
    public function updateParent($oldParentId = 0, $newParentId = 0, $includeRoot = true);
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Api\ApiInterface;

use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

/**
 * CategoryPermissionApiInterface
 */
interface CategoryPermissionApiInterface
{
    /**
     * Check for access to a certain set of categories.
     *
     * For each category property in the list, check if we have access to that category in that property.
     * Check is done as "ZikulaCategoriesModule:PropertyId:CategoryId", "$regId::$catId"
     *
     * @param AbstractCategoryAssignment[] $categoryAssignments
     * @param int $permLevel
     * @param bool $requireAccessForAll
     * @return bool True if access is allowed to at least one of the categories
     */
    public function hasCategoryAccess(array $categoryAssignments, $permLevel = ACCESS_OVERVIEW, $requireAccessForAll = false);
}

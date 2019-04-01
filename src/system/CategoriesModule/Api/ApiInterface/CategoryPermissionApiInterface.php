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

namespace Zikula\CategoriesModule\Api\ApiInterface;

use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

interface CategoryPermissionApiInterface
{
    /**
     * Check for access to a certain set of categories.
     *
     * For each category property in the list, check if we have access to that category in that property.
     * Check is done as "ZikulaCategoriesModule:PropertyId:CategoryId", "$regId::$catId"
     *
     * @param AbstractCategoryAssignment[] $categoryAssignments
     */
    public function hasCategoryAccess(
        array $categoryAssignments,
        int $permLevel = ACCESS_OVERVIEW,
        bool $requireAccessForAll = false
    ): bool;
}

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

namespace Zikula\CategoriesBundle\Api\ApiInterface;

use Zikula\CategoriesBundle\Entity\AbstractCategoryAssignment;

interface CategoryPermissionApiInterface
{
    /**
     * Check for access to a certain set of categories.
     *
     * For each category property in the list, check if we have access to that category in that property.
     * Check is done as "ZikulaCategoriesBundle:PropertyId:CategoryId", "$regId::$catId"
     *
     * @param AbstractCategoryAssignment[] $categoryAssignments
     */
    public function hasCategoryAccess(
        array $categoryAssignments,
        int $permLevel = ACCESS_OVERVIEW,
        bool $requireAccessForAll = false
    ): bool;
}

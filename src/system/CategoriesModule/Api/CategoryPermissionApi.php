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

namespace Zikula\CategoriesModule\Api;

use InvalidArgumentException;
use Zikula\CategoriesModule\Api\ApiInterface\CategoryPermissionApiInterface;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class CategoryPermissionApi implements CategoryPermissionApiInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    public function __construct(PermissionApiInterface $permissionApi)
    {
        $this->permissionApi = $permissionApi;
    }

    public function hasCategoryAccess(
        array $categoryAssignments,
        int $permLevel = ACCESS_OVERVIEW,
        bool $requireAccessForAll = false
    ): bool {
        // Always allow access to content with no categories associated
        if (0 === count($categoryAssignments)) {
            return true;
        }

        $accessGranted = true;
        /** @var AbstractCategoryAssignment[] $categoryAssignments */
        foreach ($categoryAssignments as $categoryAssignment) {
            if (!($categoryAssignment instanceof AbstractCategoryAssignment)) {
                throw new InvalidArgumentException('$categoryAssignments must be an array of AbstractCategoryAssignment');
            }
            $regId = $categoryAssignment->getCategoryRegistryId();
            $catId = $categoryAssignment->getCategory()->getId();
            $hasAccess = $this->permissionApi->hasPermission('ZikulaCategoriesModule:PropertyId:CategoryId', "${regId}::${catId}", $permLevel);
            if ($requireAccessForAll && !$hasAccess) {
                return false;
            }
            if (!$requireAccessForAll) {
                if ($hasAccess) {
                    return true;
                }
                $accessGranted = false;
            }
        }

        return $accessGranted;
    }
}

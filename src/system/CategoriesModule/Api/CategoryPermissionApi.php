<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Api;

use Zikula\CategoriesModule\Api\ApiInterface\CategoryPermissionApiInterface;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

/**
 * CategoryPermissionApi
 */
class CategoryPermissionApi implements CategoryPermissionApiInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * CategoryPermissionApi constructor.
     *
     * @param PermissionApiInterface $permissionApi
     */
    public function __construct(PermissionApiInterface $permissionApi)
    {
        $this->permissionApi = $permissionApi;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCategoryAccess(array $categoryAssignments, $permLevel = ACCESS_OVERVIEW, $requireAccessForAll = false)
    {
        // Always allow access to content with no categories associated
        if (0 == count($categoryAssignments)) {
            return true;
        }

        $accessGranted = true;
        /** @var AbstractCategoryAssignment[] $categoryAssignments */
        foreach ($categoryAssignments as $categoryAssignment) {
            if (!($categoryAssignment instanceof AbstractCategoryAssignment)) {
                throw new \InvalidArgumentException('$categoryAssignments must be an array of AbstractCategoryAssignment');
            }
            $regId = $categoryAssignment->getCategoryRegistryId();
            $catId = $categoryAssignment->getCategory()->getId();
            $hasAccess = $this->permissionApi->hasPermission("ZikulaCategoriesModule:PropertyId:CategoryId", "$regId::$catId", $permLevel);
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

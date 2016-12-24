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

use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * CategoryPermissionApi
 */
class CategoryPermissionApi
{
    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var VariableApi
     */
    protected $variableApi;

    /**
     * CategoryPermissionApi constructor.
     *
     * @param PermissionApi $permissionApi PermissionApi service instance
     * @param VariableApi   $variableApi   VariableApi service instance
     */
    public function __construct(PermissionApi $permissionApi, VariableApi $variableApi)
    {
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
    }

    /**
     * Check for access to a certain set of categories.
     *
     * For each category property in the list, check if we have access to that category in that property.
     * Check is done as "ZikulaCategoriesModule:Property:$propertyName", "$cat[id]::"
     *
     * @param array   $categories Array of category data
     * @param integer $permLevel  Required permision level
     *
     * @return bool True if access is allowed to at least one of the categories
     */
    public function hasCategoryAccess($categories, $permLevel = ACCESS_OVERVIEW)
    {
        // Always allow access to content with no categories associated
        if (count($categories) == 0) {
            return true;
        }

        // Check if access is required for all categories or for at least one category
        $requireAccessForAll = $this->variableApi->get('ZikulaCategoriesModule', 'permissionsall', 0);

        $accessGranted = true;
        foreach ($categories as $propertyName => $cats) {
            $categoriesForProperty = is_array($cats) ? $cats : [$cats];
            foreach ($categoriesForProperty as $cat) {
                $hasAccess = $this->permissionApi->hasPermission("ZikulaCategoriesModule:$propertyName:Category", "$cat[id]:$cat[path]:$cat[ipath]", $permLevel);
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
        }

        return $accessGranted;
    }
}

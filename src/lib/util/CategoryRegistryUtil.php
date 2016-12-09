<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ServiceUtil;

/**
 * CategoryRegistryUtil
 *
 * @deprecated remove at Core-2.0
 */
class CategoryRegistryUtil
{
    /**
     * Delete a category registry entry.
     *
     * @param string  $modName    The module to create a property for
     * @param integer $categoryId The category id to bind this property to
     *
     * @return boolean True on success, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public static function deleteEntry($modName, $categoryId = null)
    {
        @trigger_error('CategoryRegistryUtil is deprecated. please use the new category registry api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category_registry')->deleteRegistry($modName, $categoryId);
    }

    /**
     * Create a category registry entry.
     *
     * @param string  $modName    The module to create a property for
     * @param string  $entityName The module entity to create a property for
     * @param string  $property   The property name
     * @param integer $categoryId The category-id to bind this property to
     *
     * @return boolean True on success, false otherwise
     */
    public static function insertEntry($modName, $entityName, $property, $categoryId)
    {
        @trigger_error('CategoryRegistryUtil is deprecated. please use the new category registry api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category_registry')->insertRegistry($modName, $entityName, $property, $categoryId);
    }

    /**
     * Update a category registry entry.
     *
     * @param integer $registryId The id of the existing entry we wish to update
     * @param string  $modName    The module to create a property for
     * @param string  $entityName The module entity to create a property for
     * @param string  $property   The property name
     * @param integer $categoryId The category-id to bind this property to
     *
     * @return boolean True on success, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public static function updateEntry($registryId, $modName, $entityName, $property, $categoryId)
    {
        @trigger_error('CategoryRegistryUtil is deprecated. please use the new category registry api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category_registry')->updateRegistry($registryId, $modName, $entityName, $property, $categoryId);
    }

    /**
     * Register a module category.
     *
     * @param array $registryData The array of category map data objects
     *
     * @return boolean True on success, false otherwise
     */
    public static function registerModuleCategory($registryData)
    {
        @trigger_error('CategoryRegistryUtil is deprecated. please use the new category registry api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category_registry')->registerModuleCategory($registryData);
    }

    /**
     * Register multiple module categories.
     *
     * @param array $registryDataArray The array of category map data objects
     *
     * @return boolean
     */
    public static function registerModuleCategories($registryDataArray)
    {
        @trigger_error('CategoryRegistryUtil is deprecated. please use the new category registry api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category_registry')->registerModuleCategories($registryDataArray);
    }

    /**
     * Get registered categories for a module.
     *
     * @param string $modName    The module name
     * @param string $entityName The entity name for which we wish to get the property for
     * @param string $arrayKey   Property name used to index the result array
     *
     * @return array The associative field array of registered categories for the specified module
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public static function getRegisteredModuleCategories($modName, $entityName, $arrayKey = 'property')
    {
        @trigger_error('CategoryRegistryUtil is deprecated. please use the new category registry api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category_registry')->getModuleCategoryIds($modName, $entityName, $arrayKey);
    }

    /**
     * Get registered category for module property.
     *
     * @param string $modName    The module we wish to get the property for
     * @param string $entityName The entity name for which we wish to get the property for
     * @param string $property   The property name
     * @param string $default    The default value to return if the requested value is not set (optional) (default=null)
     *
     * @return array The associative field array of registered categories for the specified module
     */
    public static function getRegisteredModuleCategory($modName, $entityName, $property, $default = null)
    {
        @trigger_error('CategoryRegistryUtil is deprecated. please use the new category registry api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category_registry')->getModuleCategoryId($modName, $entityName, $property, $default);
    }

    /**
     * Get the IDs of the property registers.
     *
     * @param string $modName    The module name
     * @param string $entityName The entity name for which we wish to get the property for
     *
     * @return array The associative field array of register ids for the specified module
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public static function getRegisteredModuleCategoriesIds($modName, $entityName)
    {
        @trigger_error('CategoryRegistryUtil is deprecated. please use the new category registry api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category_registry')->getModuleRegistriesIds($modName, $entityName);
    }
}

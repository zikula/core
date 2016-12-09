<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule;

use ServiceUtil;
use Zikula\CategoriesModule\Entity\CategoryEntity;

/**
 * Helper functions for the categories module.
 *
 * @deprecated remove at Core-2.0
 */
class GenericUtil
{
    /**
     * Validate the data for a category
     *
     * @param array $data The data for the category
     *
     * @return boolean true/false Whether the provided data is valid
     *
     * @throws \InvalidArgumentException Thrown if no category name is provided or
     *                                          if no parent is defined for the category
     * @throws \RuntimeException Thrown if a category of the same anme already exists under the parent
     */
    public static function validateCategoryData($data)
    {
        @trigger_error('GenericUtil is deprecated. please use the new category processing helper service instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_processing_helper')->validateCategoryData($data);
    }

    /**
     * Process the name of a category
     *
     * @param array $name The name of the category
     *
     * @return string the processed name
     */
    public static function processCategoryName($name)
    {
        @trigger_error('GenericUtil is deprecated. please use the new category processing helper service instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_processing_helper')->processCategoryName($name);
    }

    /**
     * Process the parent of a category
     *
     * @param integer $parentId The parent_id of the category
     *
     * @return CategoryEntity the parent entity
     */
    public static function processCategoryParent($parentId)
    {
        @trigger_error('GenericUtil is deprecated. please use the new category processing helper service instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_processing_helper')->processCategoryParent($parentId);
    }

    /**
     * Process the display name of a category
     *
     * @param array $displayName The display name of the category
     * @param array $name        The name of the category
     *
     * @return array the processed display name
     */
    public static function processCategoryDisplayName($displayName, $name)
    {
        @trigger_error('GenericUtil is deprecated. please use the new category processing helper service instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_processing_helper')->processCategoryDisplayName($displayName, $name);
    }

    /**
     * Process the path of a category
     *
     * @param string $parentPath   The path of the parent category
     * @param string $categoryName The name of the category
     *
     * @return string the category path
     */
    public static function processCategoryPath($parentPath, $categoryName)
    {
        @trigger_error('GenericUtil is deprecated. please use the new category processing helper service instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_processing_helper')->processCategoryPath($parentPath, $categoryName);
    }

    /**
     * Process the ipath of a category
     *
     * @param string $parentIpath  The ipath of the parent category
     * @param string $categoryId   The id of the category
     *
     * @return string the category path
     */
    public static function processCategoryIPath($parentIpath, $categoryId)
    {
        @trigger_error('GenericUtil is deprecated. please use the new category processing helper service instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_processing_helper')->processCategoryIPath($parentIpath, $categoryId);
    }

    /**
     * Process the attributes of a category
     *
     * @param CategoryEntity $category     The category to set the attributes for
     * @param array          $attribNames  The attribute names
     * @param array          $attribValues The attribute values
     *
     * @return void
     */
    public static function processCategoryAttributes($category, $attribNames, $attribValues)
    {
        @trigger_error('GenericUtil is deprecated. please use the new category processing helper service instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_processing_helper')->processCategoryAttributes($category, $attribNames, $attribValues);
    }

    /**
     * Checks whether a category may be deleted or moved.
     * For this all registries are checked for if the given category is contained in the corresponding subtree.
     * If yes, the mapping table of the corresponding module is checked for if it contains the given category.
     *
     * @param CategoryEntity $category The category to process
     *
     * @return boolean true if category may be deleted or moved, false otherwise
     */
    public static function mayCategoryBeDeletedOrMoved($category)
    {
        @trigger_error('GenericUtil is deprecated. please use the new category processing helper service instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_processing_helper')->mayCategoryBeDeletedOrMoved($category);
    }
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\CategoriesModule\Entity\CategoryEntity;

/**
 * CategoryUtil.
 *
 * @deprecated remove at Core-2.0
 */
class CategoryUtil
{
    /**
     * Return a category object by ID
     *
     * @param string $rootPath    The path of the parent category
     * @param string $name        The name of the category
     * @param string $value       The value of the category (optional) (default=null)
     * @param string $displayname The displayname of the category (optional) (default=null, uses $name)
     * @param string $description The description of the category (optional) (default=null, uses $name)
     * @param string $attributes  The attributes array to bind to the category (optional) (default=null)
     *
     * @return array|boolean resulting folder object
     */
    public static function createCategory($rootPath, $name, $value = null, $displayname = null, $description = null, $attributes = null)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->createCategory($rootPath, $name, $value, $displayname, $description, $attributes);
    }

    /**
     * Return a category object by ID.
     *
     * @param integer $cid The category-ID to retrieve
     *
     * @return array resulting object or empty array if not found
     */
    public static function getCategoryByID($cid)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getCategoryById($cid);
    }

    /**
     * Return an array of categories objects according the specified where-clause and sort criteria.
     *
     * @param string  $where       The where clause to use in the select (optional) (default='')
     * @param string  $sort        The order-by clause to use in the select (optional) (default='')
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='')
     * @param array   $columnArray Array of columns to select (optional) (default=null)
     *
     * @return array resulting category object array
     */
    public static function getCategories($where = '', $sort = '', $assocKey = '', $columnArray = null)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getCategories($where, $sort, $assocKey, $columnArray);
    }

    /**
     * Return a category object by it's path
     *
     * @param string $apath     The path to retrieve by (simple path or array of paths)
     * @param string $pathField The (path) field we search for (either path or ipath) (optional) (default='path')
     *
     * @return array resulting category object
     */
    public static function getCategoryByPath($apath, $pathField = 'path')
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getCategoryByPath($apath, $pathField);
    }

    /**
     * Return an array of categories by the registry info.
     *
     * @param array $registry The registered categories to retrieve
     *
     * @return array resulting folder object array
     */
    public static function getCategoriesByRegistry($registry)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getCategoriesByRegistry($registry);
    }

    /**
     * Return the direct subcategories of the specified category
     *
     * @param integer $id         The folder id to retrieve
     * @param string  $sort       The order-by clause (optional) (default='')
     * @param boolean $relative   Whether or not to also generate relative paths (optional) (default=false)
     * @param boolean $all        Whether or not to return all (or only active) categories (optional) (default=false)
     * @param string  $assocKey   The field to use as the associated array key (optional) (default='')
     * @param array   $attributes The associative array of attribute field names to filter by (optional) (default=null)
     *
     * @return array resulting folder object
     */
    public static function getCategoriesByParentID($id, $sort = '', $relative = false, $all = false, $assocKey = '', $attributes = null)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getCategoriesByParentId($id, $sort, $relative, $all, $assocKey = '', $attributes);
    }

    /**
     * Return all parent categories starting from id.
     *
     * @param integer        $id       The (leaf) folder id to retrieve
     * @param string|boolean $assocKey Whether or not to return an associative array (optional) (default='id')
     *
     * @return array resulting folder object array
     */
    public static function getParentCategories($id, $assocKey = 'id')
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getParentCategories($id, $assocKey);
    }

    /**
     * Return an array of category objects by path without the root category
     *
     * @param string  $apath       The path to retrieve categories by
     * @param string  $sort        The sort field (optional) (default='')
     * @param string  $pathField   The (path) field to use (path or ipath) (optional) (default='ipath')
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true)
     * @param boolean $all         Whether or not to return all (or only active) categories (optional) (default=false)
     * @param string  $exclPath    The path to exclude from the retrieved categories (optional) (default='')
     * @param string  $assocKey    The field to use to build an associative key (optional) (default='')
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null)
     * @param array   $columnArray The list of columns to fetch (optional) (default=null)
     *
     * @return array resulting folder object array
     */
    public static function getCategoriesByPath($apath, $sort = '', $pathField = 'ipath', $includeLeaf = true, $all = false, $exclPath = '', $assocKey = '', $attributes = null, $columnArray = null)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getCategoriesByPath($apath, $sort, $pathField, $includeLeaf, $all, $exclPath, $assocKey, $attributes, $columnArray);
    }

    /**
     * Return an array of Subcategories for the specified folder
     *
     * @param integer $cid         The root-category category-id
     * @param boolean $recurse     Whether or not to generate a recursive subcategory result set (optional) (default=true)
     * @param boolean $relative    Whether or not to generate relative path indexes (optional) (default=true)
     * @param boolean $includeRoot Whether or not to include the root folder in the result set (optional) (default=false)
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true)
     * @param boolean $all         Whether or not to include all (or only active) folders in the result set (optional) (default=false)
     * @param integer $excludeCid  CategoryID (root folder) to exclude from the result set (optional) (default='')
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='')
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null)
     * @param string  $sortField   The field to sort the resulting category array by (optional) (default='sort_value')
     * @param array   $columnArray The list of columns to fetch (optional) (default=null)
     *
     * @return array the resulting folder object array
     */
    public static function getSubCategories($cid, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null, $sortField = 'sort_value', $columnArray = null)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getSubCategories($cid, $recurse, $relative, $includeRoot, $includeLeaf, $all, $excludeCid, $assocKey, $attributes, $sortField, $columnArray);
    }

    /**
     * Return an array of Subcategories for the specified folder
     *
     * @param string  $apath       The path to get categories by
     * @param string  $pathField   The (path) field we match by (either path or ipath) (optional) (default='ipath')
     * @param boolean $recurse     Whether or not to generate a recursive subcategory result set (optional) (default=true)
     * @param boolean $relative    Whether or not to generate relative path indexes (optional) (default=true)
     * @param boolean $includeRoot Whether or not to include the root folder in the result set (optional) (default=false)
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true)
     * @param boolean $all         Whether or not to include all (or only active) folders in the result set (optional) (default=false)
     * @param integer $excludeCid  CategoryID (root folder) to exclude from the result set (optional) (default='')
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='')
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null)
     * @param string  $sortField   The field to sort the resulting category array by (optional) (default='sort_value')
     *
     * @return array resulting folder object array
     */
    public static function getSubCategoriesByPath($apath, $pathField = 'ipath', $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null, $sortField = 'sort_value')
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getSubCategoriesByPath($apath, $pathField, $recurse, $relative, $includeRoot, $includeLeaf, $all, $excludeCid, $assocKey, $attributes, $sortField);
    }

    /**
     * Return an array of Subcategories by for the given category
     *
     * @param array   $category    The root category to retrieve
     * @param boolean $recurse     Whether or not to recurse (if false, only direct subfolders are retrieved) (optional) (default=true)
     * @param boolean $relative    Whether or not to also generate relative paths (optional) (default=true)
     * @param boolean $includeRoot Whether or not to include the root folder in the result set (optional) (default=false)
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true)
     * @param boolean $all         Whether or not to return all (or only active) categories (optional) (default=false)
     * @param string  $excludeCat  The root category of the hierarchy to exclude from the result set (optional) (default='')
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='')
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null)
     * @param string  $sortField   The field to sort the resulting category array by (optional) (default='sort_value')
     * @param array   $columnArray The list of columns to fetch (optional) (default=null)
     *
     * @return array resulting folder object array
     */
    public static function getSubCategoriesForCategory($category, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCat = null, $assocKey = '', $attributes = null, $sortField = 'sort_value', $columnArray = null)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->getSubCategoriesForCategory($category, $recurse, $relative, $includeRoot, $includeLeaf, $all, $excludeCat, $assocKey, $attributes, $sortField, $columnArray);
    }

    /**
     * Delete a category by it's ID
     *
     * @param integer $cid The categoryID to delete
     *
     * @return boolean|void
     */
    public static function deleteCategoryByID($cid)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->deleteCategoryById($cid);
    }

    /**
     * Delete categories by path
     *
     * @param string $apath     The path we wish to delete
     * @param string $pathField The (path) field we delete from (either path or ipath) (optional) (default='ipath')
     *
     * @return boolean|void
     */
    public static function deleteCategoriesByPath($apath, $pathField = 'ipath')
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category')->deleteCategoriesByPath($apath, $pathField);
    }

    /**
     * Move categories by ID (recursive move).
     *
     * @param integer $cid         The categoryID we wish to move
     * @param integer $newParentId The categoryID of the new parent category
     *
     * @return boolean
     */
    public static function moveCategoriesByID($cid, $newParentId)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new copy and move helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.copy_and_move_helper')->moveCategoriesById($cid, $newParentId);
    }

    /**
     * Move SubCategories by path (recursive move).
     *
     * @param string  $apath       The path to move from
     * @param integer $newParentId The categoryID of the new parent category
     * @param string  $pathField   The field to use for the path reference (optional) (default='ipath')
     *
     * @return boolean
     */
    public static function moveSubCategoriesByPath($apath, $newParentId, $pathField = 'ipath')
    {
        @trigger_error('CategoryUtil is deprecated. please use the new copy and move helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.copy_and_move_helper')->moveSubCategoriesByPath($apath, $newParentId, $pathField);
    }

    /**
     * Move Categories by path (recursive move).
     *
     * @param string  $apath       The path to move from
     * @param integer $newParentId The categoryID of the new parent category
     * @param string  $pathField   The field to use for the path reference (optional) (default='ipath')
     * @param boolean $includeRoot Whether or not to also move the root folder  (optional) (default=true)
     *
     * @return boolean
     */
    public static function moveCategoriesByPath($apath, $newParentId, $pathField = 'ipath', $includeRoot = true)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new copy and move helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.copy_and_move_helper')->moveCategoriesByPath($apath, $newParentId, $pathField, $includeRoot);
    }

    /**
     * Copy categories by ID (recursive copy).
     *
     * @param integer $cid         The categoryID we wish to copy
     * @param integer $newParentId The categoryID of the new parent category
     *
     * @return boolean
     */
    public static function copyCategoriesByID($cid, $newParentId)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new copy and move helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.copy_and_move_helper')->copyCategoriesById($cid, $newParentId);
    }

    /**
     * Copy subcategories by path (recursive copy).
     *
     * @param string  $apath       The path to copy from
     * @param integer $newParentId The categoryID of the new parent category
     * @param string  $pathField   The field to use for the path reference (optional) (default='ipath')
     *
     * @return boolean
     */
    public static function copySubCategoriesByPath($apath, $newParentId, $pathField = 'ipath')
    {
        @trigger_error('CategoryUtil is deprecated. please use the new copy and move helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.copy_and_move_helper')->copySubCategoriesByPath($apath, $newParentId, $pathField);
    }

    /**
     * Copy categories by path (recursive copy).
     *
     * @param string  $apath       The path to copy from
     * @param integer $newParentId The categoryID of the new parent category
     * @param string  $pathField   The field to use for the path reference (optional) (default='ipath')
     * @param boolean $includeRoot Whether or not to also move the root folder (optional) (default=true)
     *
     * @return boolean
     */
    public static function copyCategoriesByPath($apath, $newParentId, $pathField = 'ipath', $includeRoot = true)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new copy and move helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.copy_and_move_helper')->copyCategoriesByPath($apath, $newParentId, $pathField, $includeRoot);
    }

    /**
     * Check whether $cid is a direct subcategory of $rootId.
     *
     * @param integer $rootId The root/parent ID
     * @param integer $cid    The categoryID we wish to check for subcategory-ness
     *
     * @return boolean
     */
    public static function isDirectSubCategoryByID($rootId, $cid)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new hierarchy helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.hierarchy_helper')->isDirectSubCategoryById($rootId, $cid);
    }

    /**
     * Check whether $cid is a direct subcategory of $rootId.
     *
     * @param array $rootCat The root/parent category
     * @param array $cat     The category we wish to check for subcategory-ness
     *
     * @return boolean
     */
    public static function isDirectSubCategory($rootCat, $cat)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new hierarchy helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.hierarchy_helper')->isDirectSubCategory($rootCat, $cat);
    }

    /**
     * Check whether $cid is a subcategory of $rootId.
     *
     * @param integer $rootId The ID of the root category we wish to check from
     * @param integer $cid    The category-id we wish to check for subcategory-ness
     *
     * @return boolean
     */
    public static function isSubCategoryByID($rootId, $cid)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new hierarchy helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.hierarchy_helper')->isSubCategoryById($rootId, $cid);
    }

    /**
     * Check whether $cat is a subcategory of $rootCat.
     *
     * @param array $rootCat The root/parent category
     * @param array $cat     The category we wish to check for subcategory-ness
     *
     * @return boolean
     */
    public static function isSubCategory($rootCat, $cat)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new hierarchy helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.hierarchy_helper')->isSubCategory($rootCat, $cat);
    }

    /**
     * Check whether the category $cid has subcategories (optional checks for leafe ).
     *
     * @param integer $cid       The parent category
     * @param boolean $countOnly Whether or not to explicitly check for leaf nodes in the subcategories
     * @param boolean $all       Whether or not to return all (or only active) subcategories
     *
     * @return boolean
     */
    public static function haveDirectSubcategories($cid, $countOnly = false, $all = true)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new hierarchy helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.hierarchy_helper')->hasDirectSubcategories($cid, $countOnly, $all);
    }

    /**
     * Get the java-script for the tree menu.
     *
     * @param array   $cats             The categories array to represent in the tree
     * @param boolean $doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true)
     * @param boolean $sortable         Sets the zikula tree option sortable (optional) (default=false)
     * @param array   $options          Options array for Zikula_Tree
     *
     * @return string generated tree JS text
     */
    public static function getCategoryTreeJS($cats, $doReplaceRootCat = true, $sortable = false, array $options = [])
    {
        @trigger_error('CategoryUtil is deprecated. please use the js tree helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.js_tree_helper')->getCategoryTreeJs($cats, $doReplaceRootCat, $sortable, $options);
    }

    /**
     * Get the java-script for the tree menu using jQuery.
     *
     * @param array   $cats             The categories array to represent in the tree
     * @param boolean $doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true)
     * @param boolean $sortable         Sets the zikula tree option sortable (optional) (default=false)
     * @param array   $options          Options array for Zikula_Tree
     *
     * @return string generated tree JS text
     */
    public static function getCategoryTreeJqueryJS($cats, $doReplaceRootCat = true, $sortable = false, array $options = [])
    {
        @trigger_error('CategoryUtil is deprecated. please use the js tree helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.js_tree_helper')->getCategoryTreeJqueryJs($cats, $doReplaceRootCat, $sortable, $options);
    }

    /**
     * create a JSON formatted object compatible with jsTree node structure for one category (includes children)
     *
     * @param CategoryEntity $category
     * @return array
     */
    public static function getJsTreeNodeFromCategory(CategoryEntity $category)
    {
        @trigger_error('CategoryUtil is deprecated. please use the js tree helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.js_tree_helper')->getJsTreeNodeFromCategory($category);
    }

    /**
     * create a JSON formatted object compatible with jsTree node structure from an array of categories
     *
     * @param $categories
     * @return array
     */
    public static function getJsTreeNodeFromCategoryArray($categories)
    {
        @trigger_error('CategoryUtil is deprecated. please use the js tree helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.js_tree_helper')->getJsTreeNodeFromCategoryArray($categories);
    }

    /**
     * Prepare category for the tree menu.
     *
     * @param array $category Category data
     *
     * @return array Prepared category data
     */
    public static function getCategoryTreeJSNode($category)
    {
        @trigger_error('CategoryUtil is deprecated. please use the js tree helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.js_tree_helper')->getCategoryTreeJsNode($category);
    }

    /**
     * insert one leaf in a category tree (path as keys) recursively.
     *
     * Example:
     * $tree[name] = array of children
     * $tree[name]['_/_'] = branch/leaf data.
     *
     * @param array  &$tree       Tree or branch
     * @param array  $entry       The entry to insert
     * @param string $currentpath The current path to use (optional) (default=$entry['ipath'])
     *
     * @return array Tree
     */
    public static function _tree_insert(&$tree, $entry, $currentpath = null)
    {
        @trigger_error('CategoryUtil is deprecated. please use the category sorting helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_sorting_helper')->insertTreeLeaf($tree, $entry, $currentpath);
    }

    /**
     * make a list, sorted on each level, from a tree.
     *
     * @param array $tree Nested array from _tree_insert
     * @param array &$cats List of categories (initially empty array)
     *
     * @return void
     */
    public static function _tree_sort($tree, &$cats)
    {
        @trigger_error('CategoryUtil is deprecated. please use the category sorting helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_sorting_helper')->sortTree($tree, $cats);
    }

    /**
     * Take a raw list of category data, return it sorted on each level.
     *
     * @param array  $cats      List of categories (arrays)
     * @param string $sortField The sort field (optional)
     * @param string $assocKey  Key of category arrays (optional)
     *
     * @return array list of categories, sorted on each level
     */
    public static function sortCategories($cats, $sortField = '', $assocKey = '')
    {
        @trigger_error('CategoryUtil is deprecated. please use the category sorting helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_sorting_helper')->sortCategories($cats, $sortField, $assocKey);
    }

    /**
     * Return an array of folders the user has at least access/view rights to.
     *
     * @param array $cats List of categories
     *
     * @return array The resulting folder path array
     * @deprecated
     */
    public static function getCategoryTreeStructure($cats)
    {
        @trigger_error('CategoryUtil is deprecated. please use the html tree helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.html_tree_helper')->getCategoryTreeStructure($cats);
    }

    /**
     * Return the HTML selector code for the given category hierarchy.
     *
     * @param array        $cats             The category hierarchy to generate a HTML selector for
     * @param string       $field            The field value to return (optional) (default='id')
     * @param string|array $selectedValue    The selected category (optional) (default=0)
     * @param string       $name             The name of the selector field to generate (optional) (default='category[parent_id]')
     * @param integer      $defaultValue     The default value to present to the user (optional) (default=0)
     * @param string       $defaultText      The default text to present to the user (optional) (default='')
     * @param integer      $allValue         The value to assign to the "all" option (optional) (default=0)
     * @param string       $allText          The text to assign to the "all" option (optional) (default='')
     * @param boolean      $submit           Whether or not to submit the form upon change (optional) (default=false)
     * @param boolean      $displayPath      If false, the path is simulated, if true, the full path is shown (optional) (default=false)
     * @param boolean      $doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true)
     * @param integer      $multipleSize     If > 1, a multiple selector box is built, otherwise a normal/single selector box is build (optional) (default=1)
     * @param boolean      $fieldIsAttribute True if the field is attribute (optional) (default=false)
     *
     * @return string The HTML selector code for the given category hierarchy
     */
    public static function getSelector_Categories($cats, $field = 'id', $selectedValue = '0', $name = 'category[parent_id]', $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $submit = false, $displayPath = false, $doReplaceRootCat = true, $multipleSize = 1, $fieldIsAttribute = false, $cssClass = '', $lang = null)
    {
        @trigger_error('CategoryUtil is deprecated. please use the html tree helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.html_tree_helper')->getSelector_Categories($cats, $field, $selectedValue, $name, $defaultValue, $defaultText, $allValue = 0, $allText, $submit, $displayPath, $doReplaceRootCat, $multipleSize, $fieldIsAttribute, $cssClass, $lang);
    }

    /**
     * Compare function for ML name field.
     *
     * @param array $catA First category
     * @param array $catB Second category
     *
     * @return The resulting compare value
     */
    public static function cmpName($catA, $catB)
    {
        @trigger_error('CategoryUtil is deprecated. please use the category sorting helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_sorting_helper')->compareName($catA, $catB);
    }

    /**
     * Compare function for ML description field
     *
     * @param array $catA First category
     * @param array $catB Second category
     *
     * @return The resulting compare value
     */
    public static function cmpDesc($catA, $catB)
    {
        @trigger_error('CategoryUtil is deprecated. please use the category sorting helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_sorting_helper')->compareDesc($catA, $catB);
    }

    /**
     * Utility function to sort a category array by the current locale of   either the ML name or description.
     *
     *  The resulting sorted category array $cats updated by reference nothing is returned.
     *
     * @param array  &$cats The categories array
     * @param string $func Which compare function to use (determines field to be used for comparison) (optional) (default='compareName')
     *
     * @return void
     */
    public static function sortByLocale(&$cats, $func = 'compareName')
    {
        @trigger_error('CategoryUtil is deprecated. please use the category sorting helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_sorting_helper')->sortByLocale($cats, $func);
    }

    /**
     * Resequence the sort fields for the given category.
     *
     * @param array   $cats The categories array
     * @param integer $step The counting step/interval (optional) (default=1)
     *
     * @return true if something was done, false if an emtpy $cats was passed in
     */
    public static function resequence($cats, $step = 1)
    {
        @trigger_error('CategoryUtil is deprecated. please use the category sorting helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.category_sorting_helper')->resequence($cats, $step);
    }

    /**
     * Builds relative paths.
     *
     * Given an array of categories (with the Property-Names being
     * the keys of the array) and it corresponding Parent categories (indexed
     * with the Property-Names too), return an (identically indexed) array
     * of category-paths based on the given field (name or id make sense).
     *
     * @param array   $rootCatIDs  The root/parent categories ID
     * @param array   &$cats       The associative categories object array
     * @param boolean $includeRoot If true, the root portion of the path is preserved
     *
     * @return The resulting folder path array (which is also altered in place)
     */
    public static function buildRelativePaths($rootCatIDs, &$cats, $includeRoot = false)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new path builder helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.path_builder_helper')->buildRelativePaths($rootCatIDs, $cats, $includeRoot);
    }

    /**
     * Given a category with its parent category.
     *
     * Return an (idenically indexed) array of category-paths based on the given field (name or id make sense).
     *
     * @param integer|array $rootCategory The root/parent category
     * @param array         &$cat         The category to process
     * @param boolean $includeRoot If true, the root portion of the path is preserved
     *
     * @return The resulting folder path array (which is also altered in place)
     */
    public static function buildRelativePathsForCategory($rootCategory, &$cat, $includeRoot = false)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new relative category path builder helper instead.', E_USER_DEPRECATED);

        if (is_numeric($rootCategory)) {
            $rootCategory = ServiceUtil::get('zikula_categories_module.api.category')->getCategoryById($rootCategory);
        }

        return ServiceUtil::get('zikula_categories_module.relative_category_path_builder_helper')->buildRelativePathsForCategory($rootCategory, $cat, $includeRoot);
    }

    /**
     * Builds paths.
     *
     * Given an array of categories (with the category-IDs being
     * the keys of the array), return an (idenically indexed) array
     * of category-paths based on the given field (name or id make sense).
     *
     * @param array  $cats  The associative categories object array
     * @param string $field Which field to use the building the path (optional) (default='name')
     *
     * @return The resulting folder path array
     */
    public static function buildPaths($cats, $field = 'name')
    {
        @trigger_error('CategoryUtil is deprecated. please use the new path builder helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.path_builder_helper')->buildPaths($cats, $field);
    }

    /**
     * Rebuild the path field for all categories in the database.
     *
     * Note that field and sourceField go in pairs (that is, if you want sensical results)!.
     *
     * @param string  $pathField   The field which we wish to populate (optional) (default='path')
     * @param string  $sourceField The field we use to build the path with (optional) (default='name')
     * @param integer $leafId      The leaf-category category-id (ie: we'll rebuild the path of this category and all it's parents) (optional) (default=0)
     *
     * @return void
     */
    public static function rebuildPaths($pathField = 'path', $sourceField = 'name', $leafId = 0)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new path builder helper instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.path_builder_helper')->rebuildPaths($pathField, $sourceField, $leafId);
    }

    /**
     * Check for access to a certain set of categories.
     *
     * For each category property in the list, check if we have access to that category in that property.
     * Check is done as "ZikulaCategoriesModule:Property:$propertyName", "$cat[id]::"
     *
     * @param array   $categories Array of category data
     * @param string  $module     Not Used!
     * @param integer $permLevel  Required permision level
     *
     * @return bool True if access is allowed to at least one of the categories
     */
    public static function hasCategoryAccess($categories, $module, $permLevel = ACCESS_OVERVIEW)
    {
        @trigger_error('CategoryUtil is deprecated. please use the new category permission api instead.', E_USER_DEPRECATED);

        return ServiceUtil::get('zikula_categories_module.api.category_permission')->hasCategoryAccess($categories, $permLevel);
    }
}

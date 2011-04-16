<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * CategoryUtil.
 */
class CategoryUtil
{
    /**
     * Return a category object by ID
     *
     * @param string $rootPath    The path of the parent category.
     * @param string $name        The name of the category.
     * @param string $value       The value of the category (optional) (default=null).
     * @param string $displayname The displayname of the category (optional) (default=null, uses $name).
     * @param string $description The description of the category (optional) (default=null, uses $name).
     * @param string $attributes  The attributes array to bind to the category (optional) (default=null).
     *
     * @return The resulting folder object
     */
    public static function createCategory($rootPath, $name, $value=null, $displayname=null, $description=null, $attributes=null)
    {
        if (!isset($rootPath) || !$rootPath) {
            return LogUtil::registerError(__f("Error! Received invalid parameter '%s'", 'rootPath'));
        }
        if (!isset($name) || !$name) {
            return LogUtil::registerError(__f("Error! Received invalid parameter '%s'", 'name'));
        }

        if (!$displayname) {
            $displayname = $name;
        }
        if (!$description) {
            $description = $name;
        }

        $lang = ZLanguage::getLanguageCode();

        $rootCat = self::getCategoryByPath($rootPath);
        if (!$rootCat) {
            return LogUtil::registerError(__f("Error! Non-existing root category '%s' received", $rootPath));
        }

        $checkCat = self::getCategoryByPath("$rootPath/$name");
        if (!$checkCat) {
            $cat = new Categories_DBObject_Category();
            $data = array();
            $data['parent_id'] = $rootCat['id'];
            $data['name'] = $name;
            $data['display_name'] = array($lang => $displayname);
            $data['display_desc'] = array($lang => $description);
            if ($value) {
                $data['value'] = $value;
            }
            if ($attributes && is_array($attributes)) {
                $data['__ATTRIBUTES__'] = $attributes;
            }
            $cat->setData($data);
            if (!$cat->validate('admin')) {
                return false;
            }
            $cat->insert();
            $cat->update();
            return $cat->getDataField('id');
        }

        return false;
    }

    /**
     * Return a category object by ID.
     *
     * @param intiger $cid The category-ID to retrieve.
     *
     * @return The resulting folder object
     */
    public static function getCategoryByID($cid)
    {
        if (!$cid) {
            return false;
        }

        $permFilter = array();
        $permFilter[] = array(
            'realm' => 0,
            'component_left' => 'Categories',
            'component_middle' => '',
            'component_right' => 'Category',
            'instance_left' => 'id',
            'instance_middle' => 'path',
            'instance_right' => 'ipath',
            'level' => ACCESS_OVERVIEW);

        $result = DBUtil::selectObjectByID('categories_category', (int)$cid, 'id', null, $permFilter);

        if ($result) {
            $result['display_name'] = DataUtil::formatForDisplayHTML(unserialize($result['display_name']));
            $result['display_desc'] = DataUtil::formatForDisplayHTML(unserialize($result['display_desc']));
        }

        return $result;
    }

    /**
     * Return an array of categories objects according the specified where-clause and sort criteria.
     *
     * @param string  $where                  The where clause to use in the select (optional) (default='').
     * @param string  $sort                   The order-by clause to use in the select (optional) (default='').
     * @param string  $assocKey               The field to use as the associated array key (optional) (default='').
     * @param boolean $enablePermissionFilter Whether or not to enable the permission filter(optional) (default=false).
     * @param array   $columnArray            Array of columns to select (optional) (default=null).
     *
     * @return The resulting folder object array.
     */
    public static function getCategories($where = '', $sort = '', $assocKey = '', $enablePermissionFilter = true, $columnArray = null)
    {
        ModUtil::dbInfoLoad('Categories');
        if (!$sort) {
            $dbtables = DBUtil::getTables();
            $category_column = $dbtables['categories_category_column'];
            $sort = "ORDER BY $category_column[sort_value], $category_column[path]";
        }

        $permFilter = array();
        if ($enablePermissionFilter) {
            $permFilter[] = array(
                'realm' => 0,
                'component_left' => 'Categories',
                'component_middle' => '',
                'component_right' => 'Category',
                'instance_left' => 'id',
                'instance_middle' => 'path',
                'instance_right' => 'ipath',
                'level' => ACCESS_OVERVIEW);
        }

        $cats = DBUtil::selectObjectArray('categories_category', $where, $sort, -1, -1, $assocKey, $permFilter, null, $columnArray);

        $arraykeys = array_keys($cats);
        foreach ($arraykeys as $arraykey) {
            if ($cats[$arraykey]['display_name']) {
                $cats[$arraykey]['display_name'] = DataUtil::formatForDisplayHTML(unserialize($cats[$arraykey]['display_name']));
            }

            if (isset($cats[$arraykey]['display_desc']) && $cats[$arraykey]['display_desc']) {
                $cats[$arraykey]['display_desc'] = DataUtil::formatForDisplayHTML(unserialize($cats[$arraykey]['display_desc']));
            }

            if (!$enablePermissionFilter) {
                $cats[$arraykey]['accessible'] = SecurityUtil::checkPermission('Categories::Category', $cats[$arraykey]['id'] . ':' . $cats[$arraykey]['path'] . ':' . $cats[$arraykey]['ipath'], ACCESS_OVERVIEW);
            }
        }

        return $cats;
    }

    /**
     * Return a folder object by it's path
     *
     * @param string $apath The path to retrieve by (simple path or array of paths).
     * @param string $field The (path) field we search for (either path or ipath) (optional) (default='path').
     *
     * @return The resulting folder object
     */
    public static function getCategoryByPath($apath, $field = 'path')
    {
        ModUtil::dbInfoLoad('Categories');
        $dbtables = DBUtil::getTables();
        $category_column = $dbtables['categories_category_column'];
        if (!is_array($apath)) {
            $where = "$category_column[$field]='" . DataUtil::formatForStore($apath) . "'";
        } else {
            $where = array();
            foreach ($apath as $path) {
                $where[] = "$category_column[$field]='" . DataUtil::formatForStore($path) . "'";
            }
            $where = implode(' OR ', $where);
        }
        $cats = self::getCategories($where);

        if (isset($cats[0]) && is_array($cats[0])) {
            return $cats[0];
        }

        return $cats;
    }

    /**
     * Return an array of categories by the registry info.
     *
     * @param array $registry The registered categories to retrieve.
     *
     * @return The resulting folder object array
     */
    public static function getCategoriesByRegistry($registry)
    {
        if (!$registry || !is_array($registry)) return false;

        ModUtil::dbInfoLoad('Categories');
        $dbtables = DBUtil::getTables();
        $category_column = $dbtables['categories_category_column'];

        $where = array();
        foreach ($registry as $property => $catID) {
            $where[] = "$category_column[id]='" . DataUtil::formatForStore($catID) . "'";
        }
        $where = implode(' OR ', $where);
        $cats = self::getCategories($where, '', 'id');

        $result = array();
        if ($cats !== false) {
            foreach ($registry as $property => $catID) {
                if (isset($cats[$catID])) {
                    $result[$property] = $cats[$catID];
                }
            }
        }

        return $result;
    }

    /**
     * Return the direct subcategories of the specified category
     *
     * @param intiger $id         The folder id to retrieve.
     * @param string  $sort       The order-by clause (optional) (default='').
     * @param boolean $relative   Whether or not to also generate relative paths (optional) (default=false).
     * @param boolean $all        Whether or not to return all (or only active) categories (optional) (default=false).
     * @param string  $assocKey   The field to use as the associated array key (optional) (default='').
     * @param array   $attributes The associative array of attribute field names to filter by (optional) (default=null).
     *
     * @return The resulting folder object
     */
    public static function getCategoriesByParentID($id, $sort = '', $relative = false, $all = false, $assocKey = '', $attributes = null)
    {
        if (!$id) {
            return false;
        }

        ModUtil::dbInfoLoad('Categories');
        $dbtables = DBUtil::getTables();
        $category_column = $dbtables['categories_category_column'];

        $id = (int)$id;
        $where = "$category_column[parent_id]='" . DataUtil::formatForStore($id) . "'";

        if (!$all) {
            $where .= " AND $category_column[status]='A'";
        }

        //if ($attributes && is_array($attributes)) {
        //    foreach ($attributes as $k=>$v) {
        //        $where .= " AND $category_column[$k]='$v' ";
        //    }
        //}


        $cats = self::getCategories($where, $sort, $assocKey);

        if ($cats && $relative) {
            $category = self::getCategoryByID($id);
            $arraykeys = array_keys($cats);
            foreach ($arraykeys as $key) {
                self::buildRelativePathsForCategory($category, $cats[$key], isset($includeRoot) ? $includeRoot : false);
            }
        }

        return $cats;
    }

    /**
     * Return all parent categories starting from id.
     *
     * @param intiger        $id       The (leaf) folder id to retrieve.
     * @param string|boolean $assocKey Whether or not to return an assocKeyiative array (optional) (default='id').
     *
     * @return The resulting folder object array
     */
    public static function getParentCategories($id, $assocKey = 'id')
    {
        if (!$id) {
            return false;
        }

        ModUtil::dbInfoLoad('Categories');
        $dbtables = DBUtil::getTables();
        $category_column = $dbtables['categories_category_column'];

        $cat = self::getCategoryByID($id);
        $cats = array();

        if (!$cat || !$cat['parent_id']) {
            return $cats;
        }

        do {
            $cat = self::getCategoryByID($cat['parent_id']);
            if ($cat) {
                $cats[$cat[$assocKey]] = $cat;
            }
        } while ($cat && $cat['parent_id']);

        return $cats;
    }

    /**
     * Return an array of category objects by path without the root category
     *
     * @param string  $apath       The path to retrieve categories by.
     * @param string  $sort        The sort field (optional) (default='').
     * @param string  $field       The the (path) field to use (path or ipath) (optional) (default='ipath').
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true).
     * @param boolean $all         Whether or not to return all (or only active) categories (optional) (default=false).
     * @param string  $exclPath    The path to exclude from the retrieved categories (optional) (default='').
     * @param string  $assocKey    The field to use to build an associative key (optional) (default='').
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null).
     * @param array   $columnArray The list of columns to fetch (optional) (default=null).
     *
     * @return The resulting folder object array
     */
    public static function getCategoriesByPath($apath, $sort = '', $field = 'ipath', $includeLeaf = true, $all = false, $exclPath = '', $assocKey = '', $attributes = null, $columnArray = null)
    {
        ModUtil::dbInfoLoad('Categories');
        $dbtables = DBUtil::getTables();
        $category_column = $dbtables['categories_category_column'];

        $where = "($category_column[$field] = '" . DataUtil::formatForStore($apath) . "' OR $category_column[$field] LIKE '" . DataUtil::formatForStore($apath) . "/%')";

        if ($exclPath) {
            $where .= " AND $category_column[$field] NOT LIKE '" . DataUtil::formatForStore($exclPath) . "%'";
        }

        if (!$includeLeaf) {
            $where .= " AND $category_column[is_leaf] = 0";
        }

        if (!$all) {
            $where .= " AND $category_column[status] = 'A'";
        }

        //if ($attributes && is_array($attributes)) {
        //    foreach ($attributes as $k=>$v) {
        //        $where .= " AND $category_column[$k]='$v' ";
        //    }
        //}

        if (!$sort) {
            $sort = "ORDER BY $category_column[sort_value], $category_column[path]";
        }

        $cats = self::getCategories($where, $sort, $assocKey, null, $columnArray);
        return $cats;
    }

    /**
     * Return an array of Subcategories for the specified folder
     *
     * @param intiger $cid         The root-category category-id.
     * @param boolean $recurse     Whether or not to generate a recursive subcategory result set (optional) (default=true).
     * @param boolean $relative    Whether or not to generate relative path indexes (optional) (default=true).
     * @param boolean $includeRoot Whether or not to include the root folder in the result set (optional) (default=false).
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true).
     * @param boolean $all         Whether or not to include all (or only active) folders in the result set (optional) (default=false).
     * @param intiger $excludeCid  CategoryID (root folder) to exclude from the result set (optional) (default='').
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='').
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null).
     * @param string  $sortField   The field to sort the resulting category array by (optional) (default='sort_value').
     * @param array   $columnArray The list of columns to fetch (optional) (default=null).
     *
     * @return The resulting folder object array
     */
    public static function getSubCategories($cid, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null, $sortField = 'sort_value', $columnArray = null)
    {
        if (!$cid) {
            return false;
        }

        $rootCat = self::getCategoryByID($cid);
        if (!$rootCat) {
            return false;
        }

        $exclCat = '';
        if ($excludeCid) {
            $exclCat = self::getCategoryByID($excludeCid);
        }

        $cats = self::getSubCategoriesForCategory($rootCat, $recurse, $relative, $includeRoot, $includeLeaf, $all, $exclCat, $assocKey, $attributes, $sortField, $columnArray);
        return $cats;
    }

    /**
     * Return an array of Subcategories for the specified folder
     *
     * @param string  $apath       The path to get categories by.
     * @param string  $field       The (path) field we match by (either path or ipath) (optional) (default='ipath').
     * @param boolean $recurse     Whether or not to generate a recursive subcategory result set (optional) (default=true).
     * @param boolean $relative    Whether or not to generate relative path indexes (optional) (default=true).
     * @param boolean $includeRoot Whether or not to include the root folder in the result set (optional) (default=false).
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true).
     * @param boolean $all         Whether or not to include all (or only active) folders in the result set (optional) (default=false).
     * @param intiger $excludeCid  CategoryID (root folder) to exclude from the result set (optional) (default='').
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='').
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null).
     * @param string  $sortField   The field to sort the resulting category array by (optional) (default='sort_value').
     *
     * @return The resulting folder object array.
     */
    public static function getSubCategoriesByPath($apath, $field = 'ipath', $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null, $sortField = 'sort_value')
    {
        if (!$apath) {
            return false;
        }

        $rootCat = self::getCategoryByPath($apath, $field);
        if (!$rootCat) {
            return false;
        }

        $exclCat = '';
        if ($excludeCid) {
            $exclCat = self::getCategoryByID($excludeCid);
        }

        $cats = self::getSubCategoriesForCategory($rootCat, $recurse, $relative, $includeRoot, $includeLeaf, $all, $exclCat, $assocKey, $attributes, $sortField);
        return $cats;
    }

    /**
     * Return an array of Subcategories by for the given category
     *
     * @param array   $category    The root category to retrieve.
     * @param boolean $recurse     Whether or not to recurse (if false, only direct subfolders are retrieved) (optional) (default=true).
     * @param boolean $relative    Whether or not to also generate relative paths (optional) (default=true).
     * @param boolean $includeRoot Whether or not to include the root folder in the result set (optional) (default=false).
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true).
     * @param boolean $all         Whether or not to return all (or only active) categories (optional) (default=false).
     * @param string  $excludeCat  The root category of the hierarchy to exclude from the result set (optional) (default='').
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='').
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null).
     * @param string  $sortField   The field to sort the resulting category array by (optional) (default='sort_value').
     * @param array   $columnArray The list of columns to fetch (optional) (default=null).
     *
     * @return The resulting folder object array.
     */
    public static function getSubCategoriesForCategory($category, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCat = null, $assocKey = '', $attributes = null, $sortField = 'sort_value', $columnArray = null)
    {
        if (!$category) {
            return false;
        }

        $cats = array();
        $ipath = $category['ipath'];
        if ($recurse) {
            $ipathExcl = ($excludeCat ? $excludeCat['ipath'] : '');
            $cats = self::getCategoriesByPath($ipath, '', 'ipath', $includeLeaf, $all, $ipathExcl, $assocKey, $attributes, $columnArray);
        } else {
            $cats = self::getCategoriesByParentID($category['id'], '', $relative, $all, $assocKey, $attributes);
            array_unshift($cats, $category);
        }

        // since array_shift() resets numeric array indexes, we remove the leading element like this
        if (!$includeRoot) {
            foreach ($cats as $k => $v) {
                if (isset($v['ipath']) && $v['ipath'] == $ipath) {
                    unset($cats[$k]);
                }
            }
        }

        if ($cats && $relative) {
            $arraykeys = array_keys($cats);
            foreach ($arraykeys as $key) {
                self::buildRelativePathsForCategory($category, $cats[$key], $includeRoot);
            }
        }

        if ($sortField) {
            global $_catSortField;
            $_catSortField = $sortField;
            $cats = self::sortCategories($cats, $sortField, $assocKey);
        }

        return $cats;
    }

    /**
     * Delete a category by it's ID
     *
     * @param intiger $cid The categoryID to delete.
     *
     * @return The DB result set.
     */
    public static function deleteCategoryByID($cid)
    {
        ModUtil::dbInfoLoad('Categories');
        $dbtables = DBUtil::getTables();
        $category_table = $dbtables['categories_category'];
        $category_column = $dbtables['categories_category_column'];

        $cid = (int)$cid;
        $sql = "DELETE FROM $category_table WHERE $category_column[id] = '" . DataUtil::formatForStore($cid) . "'";
        $res = DBUtil::executeSQL($sql);

        return $res;
    }

    /**
     * Delete categories by Path
     *
     * @param string $apath The path we wish to delete.
     * @param string $field The (path) field we delete from (either path or ipath) (optional) (default='ipath').
     *
     * @return The DB result set.
     */
    public static function deleteCategoriesByPath($apath, $field = 'ipath')
    {
        if (!$apath) {
            return false;
        }

        ModUtil::dbInfoLoad('Categories');
        $dbtables = DBUtil::getTables();
        $category_table = $dbtables['categories_category'];
        $category_column = $dbtables['categories_category_column'];

        $sql = "DELETE FROM $category_table WHERE $category_column[$field] LIKE '" . DataUtil::formatForStore($apath) . "%'";
        $res = DBUtil::executeSQL($sql);

        return $res;
    }

    /**
     * Move categories by ID (recursive move).
     *
     * @param intiger $cid          The categoryID we wish to move.
     * @param intiger $newparent_id The categoryID of the new parent category.
     *
     * @return true or false.
     */
    public static function moveCategoriesByID($cid, $newparent_id)
    {
        if (!$cid) {
            return false;
        }

        $cat = self::getCategoryByID($cid);

        if (!$cat) {
            $false = false;
            return $false;
        }

        return self::moveCategoriesByPath($cat['ipath'], $newparent_id);
    }

    /**
     * Move SubCategories by Path (recurisve move).
     *
     * @param string  $apath        The path to move from.
     * @param intiger $newparent_id The categoryID of the new parent category.
     * @param string  $field        The field to use for the path reference (optional) (default='ipath').
     *
     * @return true or false.
     */
    public static function moveSubCategoriesByPath($apath, $newparent_id, $field = 'ipath')
    {
        return self::moveCategoriesByPath($apath, $newparent_id, $field, false);
    }

    /**
     * Move Categories by Path (recursive move).
     *
     * @param string  $apath        The path to move from.
     * @param intiger $newparent_id The categoryID of the new parent category.
     * @param string  $field        The field to use for the path reference (optional) (default='ipath').
     * @param boolean $includeRoot  Whether or not to also move the root folder  (optional) (default=true).
     *
     * @return true or false.
     */
    public static function moveCategoriesByPath($apath, $newparent_id, $field = 'ipath', $includeRoot = true)
    {
        if (!$apath) {
            return false;
        }

        $cats = self::getCategoriesByPath($apath, 'path', $field);
        $newParent = self::getCategoryByID($newparent_id);

        if (!$newParent || !$cats) {
            $false = false;
            return $false;
        }

        $newParentIPath = $newParent['ipath'] . '/';
        $newParentPath = $newParent['path'] . '/';

        $oldParent = self::getCategoryByID($cats[0]['parent_id']);
        $oldParentIPath = $oldParent['ipath'] . '/';
        $oldParentPath = $oldParent['path'] . '/';

        ModUtil::dbInfoLoad('Categories');
        $dbtables = DBUtil::getTables();
        $category_table = $dbtables['categories_category'];
        $category_column = $dbtables['categories_category_column'];

        $pathField = $category_column[$field];
        $fpath = $category_column['path'];
        $fipath = $category_column['ipath'];

        $sql = "UPDATE $category_table SET
                $fpath = REPLACE($fpath, '$oldParentPath', '$newParentPath'),
                $fipath = REPLACE($fipath, '$oldParentIPath', '$newParentIPath')
                WHERE $pathField = '" . DataUtil::formatForStore($apath) . "' OR $pathField LIKE '" . DataUtil::formatForStore($apath) . "/%'";
        DBUtil::executeSQL($sql);

        $pid = $cats[0]['id'];
        if ($includeRoot) {
            $sql = "UPDATE $category_table SET $category_column[parent_id] = '" . DataUtil::formatForStore($newparent_id) . "' WHERE $category_column[id] = '" . DataUtil::formatForStore($pid) . "'";
        } else {
            $sql = "UPDATE $category_table SET $category_column[parent_id] = '" . DataUtil::formatForStore($newparent_id) . "' WHERE $category_column[parent_id] = '" . DataUtil::formatForStore($pid) . "'";
        }
        DBUtil::executeSQL($sql);

        return true;
    }

    /**
     * Copy categories by ID (recursive copy).
     *
     * @param intiger $cid          The categoryID we wish to copy.
     * @param intiger $newparent_id The categoryID of the new parent category.
     *
     * @return true or false.
     */
    public static function copyCategoriesByID($cid, $newparent_id)
    {
        $cat = self::getCategoryByID($cid);

        if (!$cat) {
            return false;
        }

        return self::copyCategoriesByPath($cat['ipath'], $newparent_id);
    }

    /**
     * Copy SubCategories by Path (recurisve copy).
     *
     * @param string  $apath        The path to copy from.
     * @param intiger $newparent_id The categoryID of the new parent category.
     * @param string  $field        The field to use for the path reference (optional) (default='ipath').
     *
     * @return true or false.
     */
    public static function copySubCategoriesByPath($apath, $newparent_id, $field = 'ipath')
    {
        return self::copyCategoriesByPath($apath, $newparent_id, $field, false);
    }

    /**
     * Copy Categories by Path (recurisve copy).
     *
     * @param string  $apath        The path to copy from.
     * @param intiger $newparent_id The categoryID of the new parent category.
     * @param string  $field        The field to use for the path reference (optional) (default='ipath').
     * @param boolean $includeRoot  Whether or not to also move the root folder (optional) (default=true).
     *
     * @return true or false.
     */
    public static function copyCategoriesByPath($apath, $newparent_id, $field = 'ipath', $includeRoot = true)
    {
        if (!$apath || !$newparent_id) {
            return false;
        }

        $cats = self::getSubCategoriesByPath($apath, 'ipath', $field, true, true);
        $newParentCats = self::getSubCategories($newparent_id, true, true, true, true, true);
        $newParent = $newParentCats[0];

        if (!$newParent || !$cats) {
            return false;
        }

        $currentPaths = array();
        foreach ($newParentCats as $p) {
            $currentPaths[] = $p['path_relative'];
        }

        // need to make sure that after copying categories will have unique paths
        foreach ($cats as $k => $cat) {
            if ($includeRoot) {
                // root node is included - just check path uniqueness for root
                // subnodes will inherit it's name in paths
                $catBasePath = $newParent['path_relative'] . '/';
                if ($k === 0 && in_array($catBasePath . $cats[0]['name'], $currentPaths)) {
                    // path is not unique - add arbitrary " Copy" sufix to category name
                    $cats[0]['name'] .= ' ' . __('Copy');
                    if (in_array($catBasePath . $cats[0]['name'], $currentPaths)) {
                        // if there is already such name
                        // find first free name by adding number at the end
                        $i = 1;
                        $name = $cats[0]['name'];
                        while (in_array($catBasePath . $name, $currentPaths)) {
                            $name = $cats[0]['name'] . ' ' . $i++;
                        }
                        $cats[0]['name'] = $name;
                    }
                }
            } elseif ($k !== 0) {
                // root node is excluded - need to check each subnode if it's path will be unique
                // follow the same routin that for the root node
                $catPath = explode('/', $cat['path_relative']);
                array_shift($catPath);
                array_pop($catPath);
                $catBasePath = $newParent['path_relative'] . '/' . implode('/', $catPath);
                if (in_array($catBasePath . $cats[$k]['name'], $currentPaths)) {
                    $cats[$k]['name'] .= ' ' . __('Copy');
                    if (in_array($catBasePath . $cats[$k]['name'], $currentPaths)) {
                        $i = 1;
                        $name = $cats[$k]['name'];
                        while (in_array($catBasePath . $name, $currentPaths)) {
                            $name = $cats[$k]['name'] . ' ' . $i++;
                        }
                        $cats[$k]['name'] = $name;
                    }
                }
            }
        }

        $oldToNewID = array();
        $oldToNewID[$cats[0]['parent_id']] = $newParent['id'];

        // since array_shift() resets numeric array indexes, we remove the leading element like this
        if (!$includeRoot) {
            foreach ($cats as $k => $v) {
                if (isset($v['ipath']) && $v['ipath'] == $apath) {
                    unset($cats[$k]);
                }
            }
        }

        $ak = array_keys($cats);
        foreach ($ak as $v) {
            $cat = $cats[$v];

            $oldID = $cat['id'];
            $cat['id'] = '';
            $cat['parent_id'] = isset($oldToNewID[$cat['parent_id']]) ? $oldToNewID[$cat['parent_id']] : $newParent['id'];
            $cat['sort_value'] = null;
            $catObj = new Categories_DBObject_Category($cat);
            $catObj->insert();
            $oldToNewID[$oldID] = $catObj->_objData['id'];
        }

        // rebuild iPath since now we have all new PathIDs
        self::rebuildPaths('ipath', 'id');
        // rebuild also Pahts since names could be changed
        self::rebuildPaths();

        return true;
    }

    /**
     * Check whether $cid is a direct subcategory of $root_id.
     *
     * @param intiger $root_id The root/parent ID.
     * @param intiger $cid     The categoryID we wish to check for subcategory-ness.
     *
     * @return true or false.
     */
    public static function isDirectSubCategoryByID($root_id, $cid)
    {
        if (!$cid) {
            return false;
        }

        $cat = self::getCategoryByID($cid);

        if (isset($cat['parent_id'])) {
            return $cat['parent_id'] == $root_id;
        }

        return false;
    }

    /**
     * Check whether $cid is a direct subcategory of $root_id.
     *
     * @param array $rootCat The root/parent category.
     * @param array $cat     The category we wish to check for subcategory-ness.
     *
     * @return true or false
     */
    public static function isDirectSubCategory($rootCat, $cat)
    {
        return $cat['parent_id'] == $rootCat['id'];
    }

    /**
     * Check whether $cid is a subcategory of $root_id.
     *
     * @param intiger $root_id The ID of the root category we wish to check from.
     * @param intiger $cid     The category-id we wish to check for subcategory-ness.
     *
     * @return true or false.
     */
    public static function isSubCategoryByID($root_id, $cid)
    {
        if (!$root_id || !$cid) {
            return false;
        }

        $rootCat = self::getCategoryByID($root_id);
        $cat = self::getCategoryByID($cid);

        if (!$rootCat || !$cat) {
            return false;
        }

        return self::isSubCategory($rootCat, $cat);
    }

    /**
     * Check whether $cat is a subcategory of $rootCat.
     *
     * @param array $rootCat The root/parent category.
     * @param array $cat     The category we wish to check for subcategory-ness.
     *
     * @return true or false.
     */
    public static function isSubCategory($rootCat, $cat)
    {
        $rPath = $rootCat['ipath'] . '/';
        $cPath = $cat['ipath'];

        return strpos($cPath, $rPath) === 0;
    }

    /**
     * Check whether the category $cid has subcategories (optional checks for leafe ).
     *
     * @param intiger $cid       The parent category.
     * @param boolean $countOnly Whether or not to explicitly check for leaf nodes in the subcategories.
     * @param boolean $all       Whether or not to return all (or only active) subcategories.
     *
     * @return true or false.
     */
    public static function haveDirectSubcategories($cid, $countOnly = false, $all = true)
    {
        if (!$cid) {
            return false;
        }

        $cats = self::getCategoriesByParentID($cid, '', false, $all);

        if ($countOnly) {
            return (boolean)count($cats);
        }

        foreach ($cats as $cat) {
            if ($cat['is_leaf']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the java-script for the tree menu.
     *
     * @param array   $cats             The categories array to represent in the tree.
     * @param boolean $doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true).
     * @param boolean $sortable         Sets the zikula tree option sortable (optional) (default=false).
     * @param array   $options          Options array for Zikula_Tree.
     *
     * @return generated tree JS text.
     */
    public static function getCategoryTreeJS($cats, $doReplaceRootCat = true, $sortable = false, array $options = array())
    {
        $leafNodes = array();
        foreach ($cats as $i => $c) {
            if ($doReplaceRootCat && $c['id'] == 1 && $c['name'] == '__SYSTEM__') {
                $c['name'] = __('Root category');
            }
            $cats[$i] = self::getCategoryTreeJSNode($c);
            if ($c['is_leaf']) {
                $leafNodes[] = $c['id'];
            }
        }

        $tree = new Zikula_Tree();
        $tree->setOption('id', 'categoriesTree');
        $tree->setOption('sortable', $sortable);
        // disable drag and drop for root category
        $tree->setOption('disabled', array(1));
        $tree->setOption('disabledForDrop', $leafNodes);
        if (!empty($options)) {
            $tree->setOptionArray($options);
        }
        $tree->loadArrayData($cats);
        return $tree->getHTML();
    }

    /**
     * Prepare category for the tree menu.
     *
     * @param array $category Category data.
     *
     * @return Prepared category data.
     */
    public static function getCategoryTreeJSNode($category)
    {
        $lang = ZLanguage::getLanguageCode();
        $params = array();
        $params['mode'] = 'edit';
        $params['cid'] = $category['id'];
        $url = ModUtil::url('Categories', 'admin', 'edit', $params);

        if (FormUtil::getPassedValue('type') == 'admin') {
            $url .= '#top';
        }

        if (isset($category['display_name'][$lang]) && !empty($category['display_name'][$lang])) {
            $name = DataUtil::formatForDisplay($category['display_name'][$lang]);
            $displayName = $name;
        } else {
            $name = DataUtil::formatForDisplay($category['name']);
            $displayName = '';
        }

        $category['active'] = $category['status'] == 'A' ? true : false;
        $category['href'] = $url;

        $category['title'] = array();
        $category['title'][] = __('ID') . ": " . $category['id'];
        $category['title'][] = __('Name') . ": " . DataUtil::formatForDisplay($category['name']);
        $category['title'][] = __('Display name') . ": " . $displayName;
        $category['title'][] = __('Description') . ": " . (isset($category['display_desc'][$lang]) ? DataUtil::formatForDisplay($category['display_desc'][$lang]) : '');
        $category['title'][] = __('Value') . ": " . $category['value'];
        $category['title'][] = __('Active') . ": " . ($category['status'] == 'A' ? 'Yes' : 'No');
        $category['title'][] = __('Leaf') . ": " . ($category['is_leaf'] ? 'Yes' : 'No');
        $category['title'][] = __('Locked') . ": " . ($category['is_locked'] ? 'Yes' : 'No');
        $category['title'] = implode('&lt;br /&gt;', $category['title']);

        $category['name'] = $name;

        $category['class'] = array();
        if ($category['is_locked']) {
            $category['class'][] = 'locked';
        }
        if ($category['is_leaf']) {
            $category['class'][] = 'leaf';
        } else {
            $category['class'][] = 'z-tree-fixedparent';
        }
        $category['class'] = implode(' ', $category['class']);

        if (!$category['is_leaf']) {
            $category['icon'] = 'folder_open.png';
        }

        return $category;
    }

    /**
     * insert one leaf in a category tree (path as keys) recursively.
     *
     * Example:
     * $tree[name] = array of children
     * $tree[name]['_/_'] = branch/leaf data.
     *
     * @param array  &$tree       Tree or branch.
     * @param array  $entry       The entry to insert.
     * @param string $currentpath The current path to use (optional) (default=$entry['ipath']).
     *
     * @return array Tree.
     */
    public static function _tree_insert(&$tree, $entry, $currentpath = null)
    {
        if ($currentpath === null) {
            $currentpath = $entry['ipath'];
        }
        $currentpath = trim($currentpath, '/ ');
        $pathlist = explode('/', $currentpath);
        $root = $pathlist[0];
        if (!array_key_exists($root, $tree)) {
            $tree[$root] = array();
        }
        if (count($pathlist) == 1) {
            $tree[$root]['_/_'] = $entry;
            return $tree;
        } else {
            unset($pathlist[0]);
            self::_tree_insert($tree[$root], $entry, implode('/', $pathlist));
        }
    }

    /**
     * make a list, sorted on each level, from a tree.
     *
     * @param array $tree  Nested array from _tree_insert.
     * @param array &$cats List of categories (initially empty array).
     *
     * @return void
     */
    public static function _tree_sort($tree, &$cats)
    {
        global $_catSortField;
        $sorted = array();
        foreach ($tree as $k => $v) {
            if ($k == '_/_') {
                $cats[] = $v;
            } else {
                if (isset($v['_/_'][$_catSortField])) {
                    if ($v['_/_'][$_catSortField] > 0 && $v['_/_'][$_catSortField] < 2147483647) {
                        $sorted[$k] = $v['_/_'][$_catSortField];
                    } else {
                        $sorted[$k] = $v['_/_']['name'];
                    }
                } else {
                    $sorted[$k] = null;
                }
            }
        }

        uasort($sorted, array('self', '_tree_sort_cmp'));

        foreach ($sorted as $k => $v) {
            self::_tree_sort($tree[$k], $cats);
        }
    }

    /**
     * Internal callback function for int/string comparation.
     * 
     * It is supposed to compate integer items numerically and string items as strings,
     * so integers will be before strings (unlike SORT_REGULAR flag for array sort functions).
     * 
     * @param string $a The first value.
     * @param string $b The second value.
     * 
     * @return int 0 if $a and $b are equal, 1 ir $a is greater then $b, -1 if $a is less than $b
     */
    private static function _tree_sort_cmp($a, $b)
    {
        if ($a === $b) {
            return 0;
        }
        if (!is_numeric($a) || !is_numeric($b)) {
            return strcmp($a, $b);
        }
        return ($a < $b) ? -1 : 1;
    }

    /**
     * Take a raw list of category data, return it sorted on each level.
     *
     * @param array  $cats      List of categories (arrays).
     * @param string $sortField The sort field (optional).
     * @param string $assocKey  Key of category arrays (optional).
     *
     * @return array list of categories, sorted on each level.
     */
    public static function sortCategories($cats, $sortField = '', $assocKey = '')
    {
        if (!$cats) return $cats;

        global $_catSortField;
        if ($sortField) {
            $_catSortField = $sortField;
        } else {
            $sortField = $_catSortField;
        }

        $tree = array();
        foreach ($cats as $c) {
            self::_tree_insert($tree, $c);
        }
        $new_cats = array();
        self::_tree_sort($tree[1], $new_cats);

        if ($assocKey) {
            $new_cats_assoc = array();
            foreach ($new_cats as $c) {
                if (isset($c[$assocKey])) {
                    $new_cats_assoc[$c[$assocKey]] = $c;
                }
            }
            $new_cats = $new_cats_assoc;
        }

        return $new_cats;
    }

    /**
     * Return an array of folders the user has at least access/view rights to.
     *
     * @param array $cats List of categories.
     *
     * @return array The resulting folder path array.
     * @deprecated
     */
    public static function getCategoryTreeStructure($cats)
    {
        $menuString = '';
        $params = array();
        $params['mode'] = 'edit';

        //$cats = self::sortCategories($cats, 'sort_value');
        $lang = ZLanguage::getLanguageCode();

        foreach ($cats as $c) {
            $path = $c['path'];
            $depth = StringUtil::countInstances($path, '/');
            // account for the fact that a single slash is a valid root
            // path but subfolders only have a single slash as well
            if (strlen($path) > 1) {
                $depth++;
            }
            $ds = str_repeat('.', $depth);

            $params['cid'] = $c['id'];
            $url = DataUtil::formatForDisplay(ModUtil::url('Categories', 'admin', 'edit', $params));

            if (FormUtil::getPassedValue('type') == 'admin') {
                $url .= '#top';
            }

            if (isset($c['display_name'][$lang]) && !empty($c['display_name'][$lang])) {
                $name = DataUtil::formatForDisplay($c['display_name'][$lang]);
            } else {
                $name = DataUtil::formatForDisplay($c['name']);
            }

            $menuLine = "$ds|$name|$url||||\n";

            $menuString .= $menuLine;
        }

        //print (nl2br ($menuString));
        return $menuString;
    }

    /**
     * Return the HTML selector code for the given category hierarchy.
     *
     * @param array        $cats             The category hierarchy to generate a HTML selector for.
     * @param string       $field            The field value to return (optional) (default='id').
     * @param string|array $selectedValue    The selected category (optional) (default=0).
     * @param string       $name             The name of the selector field to generate (optional) (default='category[parent_id]').
     * @param intiger      $defaultValue     The default value to present to the user (optional) (default=0).
     * @param string       $defaultText      The default text to present to the user (optional) (default='').
     * @param intiger      $allValue         The value to assign to the "all" option (optional) (default=0).
     * @param string       $allText          The text to assign to the "all" option (optional) (default='').
     * @param boolean      $submit           Whether or not to submit the form upon change (optional) (default=false).
     * @param boolean      $displayPath      If false, the path is simulated, if true, the full path is shown (optional) (default=false).
     * @param boolean      $doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true).
     * @param intiger      $multipleSize     If > 1, a multiple selector box is built, otherwise a normal/single selector box is build (optional) (default=1).
     * @param boolean      $fieldIsAttribute True if the field is attribute (optional) (default=false).
     *
     * @return The HTML selector code for the given category hierarchy
     */
    public static function getSelector_Categories($cats, $field = 'id', $selectedValue = '0', $name = 'category[parent_id]', $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $submit = false, $displayPath = false, $doReplaceRootCat = true, $multipleSize = 1, $fieldIsAttribute = false)
    {
        $line = '---------------------------------------------------------------------';

        if ($multipleSize > 1 && strpos($name, '[]') === false) {
            $name .= '[]';
        }
        if (!is_array($selectedValue)) {
            $selectedValue = array(
                (string)$selectedValue);
        }

        $id = strtr($name, '[]', '__');
        $multiple = $multipleSize > 1 ? ' multiple="multiple"' : '';
        $multipleSize = $multipleSize > 1 ? " size=\"$multipleSize\"" : '';
        $submit = $submit ? ' onchange="this.form.submit();"' : '';
        $lang = ZLanguage::getLanguageCode();

        $html = "<select name=\"$name\" id=\"$id\"{$multipleSize}{$multiple}{$submit}>";

        if (!empty($defaultText)) {
            $sel = (in_array((string)$defaultValue, $selectedValue) ? ' selected="selected"' : '');
            $html .= "<option value=\"$defaultValue\"$sel>$defaultText</option>";
        }

        if ($allText) {
            $sel = (in_array((string)$allValue, $selectedValue) ? ' selected="selected"' : '');
            $html .= "<option value=\"$allValue\"$sel>$allText</option>";
        }

        $count = 0;
        if (!isset($cats) || empty($cats)) {
            $cats = array();
        }

        foreach ($cats as $cat) {
            if ($fieldIsAttribute) {
                $sel = (in_array((string)$cat['__ATTRIBUTES__'][$field], $selectedValue) ? ' selected="selected"' : '');
            } else {
                $sel = (in_array((string)$cat[$field], $selectedValue) ? ' selected="selected"' : '');
            }
            if ($displayPath) {
                if ($fieldIsAttribute) {
                    $v = $cat['__ATTRIBUTES__'][$field];
                    $html .= "<option value=\"$v\"$sel>$cat[path]</option>";
                } else {
                    $html .= "<option value=\"$cat[$field]\"$sel>$cat[path]</option>";
                }
            } else {
                $cslash = StringUtil::countInstances(isset($cat['ipath_relative']) ? $cat['ipath_relative'] : $cat['ipath'], '/');
                $indent = '';
                if ($cslash > 0) $indent = substr($line, 0, $cslash * 2);

                $indent = '|' . $indent;
                //if ($count) {
                //    $indent = '|' . $indent;
                //} else {
                //    $indent = '&nbsp;' . $indent;
                //}


                if (isset($cat['display_name'][$lang]) && !empty($cat['display_name'][$lang])) {
                    $catName = $cat['display_name'][$lang];
                } else {
                    $catName = $cat['name'];
                }

                if ($fieldIsAttribute) {
                    $v = $cat['__ATTRIBUTES__'][$field];
                    $html .= "<option value=\"$v\"$sel>$indent " . DataUtil::formatForDisplayHtml($catName) . "</option>";
                } else {
                    $html .= "<option value=\"$cat[$field]\"$sel>$indent " . DataUtil::formatForDisplayHtml($catName) . "</option>";
                }
            }
            $count++;
        }

        $html .= '</select>';

        if ($doReplaceRootCat) {
            $html = str_replace('__SYSTEM__', __('Root category'), $html);
        }

        return $html;
    }

    /**
     * Compare function for ML name field.
     *
     * @param array $catA First category.
     * @param array $catB Second category.
     *
     * @return The resulting compare value.
     */
    public static function cmpName($catA, $catB)
    {
        $lang = ZLanguage::getLanguageCode();

        if (!$catA['display_name'][$lang]) {
            $catA['display_name'][$lang] = $catA['name'];
        }

        if ($catA['display_name'][$lang] == $catB['display_name'][$lang]) {
            return 0;
        }

        return strcmp($catA['display_name'][$lang], $catB['display_name'][$lang]);
    }

    /**
     * Compare function for ML description field
     *
     * @param array $catA First category.
     * @param array $catB Second category.
     *
     * @return The resulting compare value
     */
    public static function cmpDesc($catA, $catB)
    {
        $lang = ZLanguage::getLanguageCode();

        if ($catA['display_desc'][$lang] == $catB['display_desc'][$lang]) {
            return 0;
        }

        return strcmp($catA['display_desc'][$lang], $catB['display_desc'][$lang]);
    }

    /**
     * Utility function to sort a category array by the current locale of   either the ML name or description.
     *
     *  The resulting sorted category array $cats updated by reference nothing is returned.
     *
     * @param array  &$cats The categories array.
     * @param string $func  Which compare function to use (determines field to be used for comparison) (optional) (defaylt='cmpName').
     *
     * @return void
     */
    public static function sortByLocale(&$cats, $func = 'cmpName')
    {
        usort($cats, $func);
        return;
    }

    /**
     * Resequence the sort fields for the given category.
     *
     * @param array   $cats The categories array.
     * @param intiger $step The counting step/interval (optional) (default=1).
     *
     * @return True if something was done, false if an emtpy $cats was passed in.
     */
    public static function resequence($cats, $step = 1)
    {
        if (!$cats) {
            return false;
        }

        $c = 0;
        $ak = array_keys($cats);
        foreach ($ak as $k) {
            $cats[$k]['sort_value'] = ++$c * $step;
        }

        return $cats;
    }

    /**
     * Builds relative paths.
     *
     * Given an array of categories (with the Property-Names being
     * the keys of the array) and it corresponding Parent categories (indexed
     * with the Property-Names too), return an (identically indexed) array
     * of category-paths based on the given field (name or id make sense).
     *
     * @param array   $rootCatIDs  The root/parent categories ID.
     * @param array   &$cats       The associative categories object array.
     * @param boolean $includeRoot If true, the root portion of the path is preserved.
     *
     * @return The resulting folder path array (which is also altered in place).
     */
    public static function buildRelativePaths($rootCatIDs, &$cats, $includeRoot = false)
    {
        if (!$rootCatIDs) {
            return false;
        }

        foreach ($cats as $prop => $catID) {
            if (!isset($rootCatIDs[$prop]) || !$rootCatIDs[$prop]) {
                continue;
            }
            $rootCat = self::getCategoryByID($rootCatIDs[$prop]);
            self::buildRelativePathsForCategory($rootCat, $cats[$prop], $includeRoot);
        }

        return;
    }

    /**
     * Given a category with its parent category.
     *
     * Return an (idenically indexed) array of category-paths based on the given field (name or id make sense).
     *
     * @param intiger|array $rootCategory The root/parent category.
     * @param array         &$cat         The category to process.
     * @param boolean       $includeRoot  If true, the root portion of the path is preserved.
     *
     * @return The resulting folder path array (which is also altered in place).
     */
    public static function buildRelativePathsForCategory($rootCategory, &$cat, $includeRoot = false)
    {
        if (!$rootCategory) {
            return false;
        }

        if (is_numeric($rootCategory)) {
            $rootCategory = self::getCategoryByID($rootCategory);
        }

        // remove the Root Category name of the paths
        // because multilanguage names has different lengths
        $pos = strpos($rootCategory['path'], '/', 1);
        $rootCategory['path'] = substr($rootCategory['path'], $pos);

        $pos = strpos($cat['path'], '/', 1);
        $normalizedPath = substr($cat['path'], $pos);

        // process the normalized paths
        $ppos = strrpos($rootCategory['path'], '/') + 1;
        $ipos = strrpos($rootCategory['ipath'], '/') + 1;

        $cat['path_relative'] = substr($normalizedPath, $ppos);
        if (isset($cat['ipath'])) {
            $cat['ipath_relative'] = substr($cat['ipath'], $ipos);
        }

        if (!$includeRoot) {
            $offSlashPath = strpos($cat['path_relative'], '/');
            if (isset($cat['ipath'])) {
                $offSlashIPath = strpos($cat['ipath_relative'], '/');
            }

            if ($offSlashPath !== false) {
                $cat['path_relative'] = substr($cat['path_relative'], $offSlashPath + 1);
            }
            if (isset($cat['ipath']) && $offSlashIPath !== false) {
                $cat['ipath_relative'] = substr($cat['ipath_relative'], $offSlashIPath + 1);
            }
        }

        return $cat;
    }

    /**
     * Builds paths.
     *
     * Given an array of categories (with the category-IDs being
     * the keys of the array), return an (idenically indexed) array
     * of category-paths based on the given field (name or id make sense).
     *
     * @param array  $cats  The associative categories object array.
     * @param string $field Which field to use the building the path (optional) (default='name').
     *
     * @return The resulting folder path array.
     */
    public static function buildPaths($cats, $field = 'name')
    {
        if (!$cats) {
            return false;
        }

        $paths = array();

        foreach ($cats as $k => $v) {
            $path = $v[$field];
            $pid = $v['parent_id'];

            while ($pid) {
                $pcat = $cats[$pid];
                $path = $pcat[$field] . '/' . $path;
                $pid = $pcat['parent_id'];
            }

            $paths[$k] = '/' . $path;
        }

        return $paths;
    }

    /**
     * Rebuild the path field for all categories in the database.
     *
     * Note that field and sourceField go in pairs (that is, if you want sensical results)!.
     *
     * @param string  $field       The field which we wish to populate (optional) (default='path').
     * @param string  $sourceField The field we use to build the path with (optional) (default='name').
     * @param integer $leaf_id     The leaf-category category-id (ie: we'll rebuild the path of this category and all it's parents) (optional) (default=0).
     *
     * @return void
     */
    public static function rebuildPaths($field = 'path', $sourceField = 'name', $leaf_id = 0)
    {
        ModUtil::dbInfoLoad('Categories');

        //if ($leaf_id)
        //$cats  = self::getParentCategories ($leaf_id, 'id');
        //else
        $cats = self::getCategories('', '', 'id');
        $paths = self::buildPaths($cats, $sourceField);

        if ($cats && $paths) {
            foreach ($cats as $k => $v) {
                if ($v[$field] != $paths[$k][$field]) {
                    $v[$field] = $paths[$k];
                    // since we're not going through the object layer for this, we must manually serialize the locale fields
                    $v['display_name'] = serialize($v['display_name']);
                    $v['display_desc'] = serialize($v['display_desc']);

                    $res = DBUtil::updateObject($v, 'categories_category');
                }
            }
        }
    }

    /**
     * Check for access to a certain set of categories.
     *
     * For each category property in the list, check if we have access to that category in that property.
     * Check is done as "Categories:Property:$propertyName", "$cat[id]::"
     *
     * @param array   $categories Array of category data (as returned from ObjectUtil::expandObjectWithCategories).
     * @param string  $module     Not Used!.
     * @param intiger $permLevel  Required permision level.
     *
     * @return bool True if access is allowed to at least one of the categories
     */
    public static function hasCategoryAccess($categories, $module, $permLevel = ACCESS_OVERVIEW)
    {
        // Always allow access to content with no categories associated
        if (count($categories) == 0) return true;

        if (ModUtil::getVar('Categories', 'permissionsall', 0)) {
            // Access is required for all categories
            $ok = true;
            foreach ($categories as $propertyName => $cat) {
                $ok = $ok && SecurityUtil::checkPermission("Categories:$propertyName:Category", "$cat[id]:$cat[path]:$cat[ipath]", $permLevel);
            }
            return $ok;
        } else {
            // Access is required for at least one category
            foreach ($categories as $propertyName => $cat) {
                if (SecurityUtil::checkPermission("Categories:$propertyName:Category", "$cat[id]:$cat[path]:$cat[ipath]", $permLevel))
                        return true;
            }

            return false;
        }
    }

}

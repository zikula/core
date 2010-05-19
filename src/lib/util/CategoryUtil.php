<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * CategoryUtil
 *
 * @package Zikula_Core
 * @subpackage CategoryUtil
 */
class CategoryUtil
{
    /**
     * Return a category object by ID
     *
     * @param rootPath     The path of the parent category
     * @param name         The name of the category
     * @param value        The value of the category (optional) (default=null)
     * @param displayname  The displayname of the category (optional) (default=null, uses $name)
     * @param description  The description of the category (optional) (default=null, uses $name)
     * @param attributes   The attributes array to bind to the category (optional) (default=null)
     *
     * @return The resulting folder object
     */
    public static function createCategory ($rootPath, $name, $value=null, $displayname=null, $description=null, $attributes=null)
    {
        if (!isset($rootPath) || !$rootpath) {
            return LogUtil (__f("Error! Received invalid parameter '%s'", 'rootpath'));
        }
        if (!isset($name) || !$name) {
            return LogUtil (__f("Error! Received invalid parameter '%s'", 'name'));
        }

        if (!$displayname) {
            $displayname = $name;
        }
        if (!$description) {
            $description = $name;
        }

        $lang = ZLanguage::getLanguageCode();

        Loader::loadClassFromModule('Categories', 'Category');
        $rootCat = CategoryUtil::getCategoryByPath ($rootPath);
        if (!$rootCat) {
            return LogUtil (__f("Error! Non-existing root category '%s' received", $rootPath));
        }

        $checkCat = CategoryUtil::getCategoryByPath ("$rootPath/$name");
        if (!$checkCat) {
            $cat  = new PNCategory();
            $data = array();
            $data['parent_id']    = $rootCat['id'];
            $data['name']         = $name;
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
     * Return a category object by ID
     *
     * @param cid      The category-ID to retrieve
     *
     * @return The resulting folder object
     */
    public static function getCategoryByID($cid)
    {
        if (!$cid) {
            return false;
        }

        ModUtil::dbInfoLoad('Categories');

        static $cache = array();
        if (isset($cache[$cid])) {
            return $cache[$cid];
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

        $cache[$cid] = DBUtil::selectObjectByID('categories_category', (int) $cid, 'id', null, $permFilter);

        $cache[$cid]['display_name'] = DataUtil::formatForDisplayHTML(unserialize($cache[$cid]['display_name']));
        $cache[$cid]['display_desc'] = DataUtil::formatForDisplayHTML(unserialize($cache[$cid]['display_desc']));

        return $cache[$cid];
    }

    /**
     * Return an array of categories objects according the specified where-clause and sort criteria.
     *
     * @param where       The where clause to use in the select (optional) (default='')
     * @param sort        The order-by clause to use in the select (optional) (default='')
     * @param assocKey    The field to use as the associated array key (optional) (default='')
     *
     * @return The resulting folder object array
     */
    public static function getCategories($where = '', $sort = '', $assocKey = '', $enablePermissionFilter = true, $columnArray = null)
    {
        ModUtil::dbInfoLoad('Categories');
        if (!$sort) {
            $pntables = System::dbGetTables();
            $category_column = $pntables['categories_category_column'];
            $sort = "ORDER BY $category_column[sort_value], $category_column[name]";
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

            if ($cats[$arraykey]['display_desc']) {
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
     * @param apath        The path to retrieve by (simple path or array of paths)
     * @param field        The (path) field we search for (either path or ipath) (optional) (default='path')
     *
     * @return The resulting folder object
     */
    public static function getCategoryByPath($apath, $field = 'path')
    {
        ModUtil::dbInfoLoad('Categories');
        $pntables = System::dbGetTables();
        $category_column = $pntables['categories_category_column'];
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
     * Return an array of categories by the registry info
     *
     * @param registry   The registered categories to retrieve
     *
     * @return The resulting folder object array
     */
    public static function getCategoriesByRegistry($registry)
    {
        if (!$registry || !is_array($registry))
            return false;

        ModUtil::dbInfoLoad('Categories');
        $pntables = System::dbGetTables();
        $category_column = $pntables['categories_category_column'];

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
     * @param id            The folder id to retrieve
     * @param sort          The order-by clause (optional) (default='')
     * @param relative      whether or not to also generate relative paths (optional) (default=false)
     * @param all           whether or not to return all (or only active) categories (optional) (default=false)
     * @param assocKey      The field to use as the associated array key (optional) (default='')
     * @param attributes    The associative array of attribute field names to filter by (optional) (default=null)
     *
     * @return The resulting folder object
     */
    public static function getCategoriesByParentID($id, $sort = '', $relative = false, $all = false, $assocKey = '', $attributes = null)
    {
        if (!$id) {
            return false;
        }

        ModUtil::dbInfoLoad('Categories');
        $pntables = System::dbGetTables();
        $category_column = $pntables['categories_category_column'];

        $id = (int) $id;
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
     * Return all parent categories starting from id
     *
     * @param id         The (leaf) folder id to retrieve
     * @param assocKey   whether or not to return an assocKeyiative array (optional) (default='id')
     *
     * @return The resulting folder object array
     */
    public static function getParentCategories($id, $assocKey = 'id')
    {
        if (!$id) {
            return false;
        }

        ModUtil::dbInfoLoad('Categories');
        $pntables = System::dbGetTables();
        $category_column = $pntables['categories_category_column'];

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
     * @param apath        The path to retrieve categories by
     * @param sort         The sort field (optional) (default='')
     * @param field        The the (path) field to use (path or ipath) (optional) (default='ipath')
     * @param includeLeaf  whether or not to also return leaf nodes (optional) (default=true)
     * @param all          whether or not to return all (or only active) categories (optional) (default=false)
     * @param exclPath     The path to exclude from the retrieved categories (optional) (default='')
     * @param assocKey     The field to use to build an associative key (optional) (default='')
     * @param attributes   The associative array of attribute field names to filter by (optional) (default=null)
     * @param columnArray  The list of columns to fetch (optional) (default=null)
     *
     * @return The resulting folder object array
     */
    public static function getCategoriesByPath($apath, $sort = '', $field = 'ipath', $includeLeaf = true, $all = false, $exclPath = '', $assocKey = '', $attributes = null, $columnArray = null)
    {
        ModUtil::dbInfoLoad('Categories');
        $pntables = System::dbGetTables();
        $category_column = $pntables['categories_category_column'];

        $where = "$category_column[$field] = '" . DataUtil::formatForStore($apath) . "' OR $category_column[$field] LIKE '" . DataUtil::formatForStore($apath) . "/%'";
        if ($exclPath) {
            $where .= " AND $category_column[$field] NOT LIKE '" . DataUtil::formatForStore($exclPath) . "%'";
        }

        if (!$includeLeaf) {
            $where .= " AND $category_column[is_leaf]=0";
        }

        if (!$all) {
            $where .= " AND $category_column[status]='A'";
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
     * @param cid          The root-category category-id
     * @param recurse      whether or not to generate a recursive subcategory result set (optional) (default=true)
     * @param relative     whether or not to generate relative path indexes (optional) (default=true)
     * @param includeRoot  whether or not to include the root folder in the result set (optional) (default=false)
     * @param includeLeaf  whether or not to also return leaf nodes (optional) (default=true)
     * @param all          whether or not to include all (or only active) folders in the result set (optional) (default=false)
     * @param excludeCid   CategoryID (root folder) to exclude from the result set (optional) (default='')
     * @param assocKey     The field to use as the associated array key (optional) (default='')
     * @param attributes   The associative array of attribute field names to filter by (optional) (default=null)
     * @param columnArray  The list of columns to fetch (optional) (default=null)
     *
     * @return The resulting folder object array
     */
    public static function getSubCategories($cid, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null, $columnArray = null)
    {
        if (!$cid) {
            return false;
        }

        $rootCat = self::getCategoryByID($cid);
        if (!$rootCat) {
            return false;
        }

        static $catPathCache = array();
        $cacheKey = $cid . '_' . (int) $recurse . '_' . (int) $relative . '_' . (int) $includeRoot . '_' . (int) $includeLeaf . '_' . (int) $all . '_' . $excludeCid . '_' . $assocKey;
        if (isset($catPathCache[$cacheKey])) {
            return $catPathCache[$cacheKey];
        }

        $exclCat = '';
        if ($excludeCid) {
            $exclCat = self::getCategoryByID($excludeCid);
        }

        $cats = self::getSubCategoriesForCategory($rootCat, $recurse, $relative, $includeRoot, $includeLeaf, $all, $exclCat, $assocKey, $attributes, '', $columnArray);
        $catPathCache[$cacheKey] = $cats;
        return $cats;
    }

    /**
     * Return an array of Subcategories for the specified folder
     *
     * @param apath        The path to get categories by
     * @param field        The (path) field we match by (either path or ipath) (optional) (default='ipath')
     * @param recurse      whether or not to generate a recursive subcategory result set (optional) (default=true)
     * @param relative     whether or not to generate relative path indexes (optional) (default=true)
     * @param includeRoot  whether or not to include the root folder in the result set (optional) (default=false)
     * @param includeLeaf  whether or not to also return leaf nodes (optional) (default=true)
     * @param all          whether or not to include all (or only active) folders in the result set (optional) (default=false)
     * @param excludeCid   CategoryID (root folder) to exclude from the result set (optional) (default='')
     * @param assocKey     The field to use as the associated array key (optional) (default='')
     * @param attributes   The associative array of attribute field names to filter by (optional) (default=null)
     *
     * @return The resulting folder object array
     */
    public static function getSubCategoriesByPath($apath, $field = 'ipath', $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null)
    {
        if (!$apath) {
            return false;
        }

        $rootCat = self::getCategoryByPath($apath, $field);
        if (!$rootCat) {
            return false;
        }

        static $catPathCache = array();
        $cacheKey = $apath . '_' . $field . '_' . (int) $recurse . '_' . (int) $relative . '_' . (int) $includeRoot . '_' . (int) $includeLeaf . '_' . (int) $all . '_' . $excludeCid . '_' . $assocKey;
        if (isset($catPathCache[$cacheKey])) {
            return $catPathCache[$cacheKey];
        }

        $exclCat = '';
        if ($excludeCid) {
            $exclCat = self::getCategoryByID($excludeCid);
        }

        $cats = self::getSubCategoriesForCategory($rootCat, $recurse, $relative, $includeRoot, $includeLeaf, $all, $exclCat, $assocKey, $attributes);
        $catPathCache[$cacheKey] = $cats;
        return $cats;
    }

    /**
     * Return an array of Subcategories by for the given category
     *
     * @param category     The root category to retrieve
     * @param recurse      whether or not to recurse (if false, only direct subfolders are retrieved) (optional) (default=true)
     * @param relative     whether or not to also generate relative paths (optional) (default=true)
     * @param includeRoot  whether or not to include the root folder in the result set (optional) (default=false)
     * @param includeLeaf  whether or not to also return leaf nodes (optional) (default=true)
     * @param all          whether or not to return all (or only active) categories (optional) (default=false)
     * @param excludeCat   The root category of the hierarchy to exclude from the result set (optional) (default='')
     * @param assocKey     The field to use as the associated array key (optional) (default='')
     * @param attributes   The associative array of attribute field names to filter by (optional) (default=null)
     * @param sortField    The field to sort the resulting category array by (optional) (default=null)
     * @param columnArray  The list of columns to fetch (optional) (default=null)
     *
     * @return The resulting folder object array
     */
    public static function getSubCategoriesForCategory($category, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCat = null, $assocKey = '', $attributes = null, $sortField = 'sort_value', $columnArray = null)
    {
        if (!$category) {
            return false;
        }

        $cats = array();
        if ($recurse) {
            $ipath = $category['ipath'];
            $ipathExcl = ($excludeCat ? $excludeCat['ipath'] : '');
            $cats = self::getCategoriesByPath($ipath, '', 'ipath', $includeLeaf, $all, $ipathExcl, $assocKey, $attributes, $columnArray);
        } else {
            $cats = self::getCategoriesByParentID($category['id'], '', $relative, $all, $assocKey, $attributes);
            array_unshift($cats, $category);
        }

        // since array_shift() resets numeric array indexes, we remove the leading element like this
        if (!$includeRoot) {
            list ($k, $v) = each($cats);
            unset($cats[$k]);
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
            $cats = self::sortCategories($cats, $sortField);
        }

        return $cats;
    }

    /**
     * Delete a category by it's ID
     *
     * @param cid        The categoryID to delete
     *
     * @return The DB result set
     */
    public static function deleteCategoryByID($cid)
    {
        ModUtil::dbInfoLoad('Categories');
        $pntables = System::dbGetTables();
        $category_table = $pntables['categories_category'];
        $category_column = $pntables['categories_category_column'];

        $cid = (int) $cid;
        $sql = "DELETE FROM $category_table WHERE $category_column[id] = '" . DataUtil::formatForStore($cid) . "'";
        $res = DBUtil::executeSQL($sql);

        return $res;
    }

    /**
     * Delete categories by Path
     *
     * @param apath        The path we wish to delete
     * @param field        The (path) field we delete from (either path or ipath) (optional) (default='ipath')
     *
     * @return The DB result set
     */
    public static function deleteCategoriesByPath($apath, $field = 'ipath')
    {
        if (!$apath) {
            return false;
        }

        ModUtil::dbInfoLoad('Categories');
        $pntables = System::dbGetTables();
        $category_table = $pntables['categories_category'];
        $category_column = $pntables['categories_category_column'];

        $sql = "DELETE FROM $category_table WHERE $category_column[$field] LIKE '" . DataUtil::formatForStore($apath) . "%'";
        $res = DBUtil::executeSQL($sql);

        return $res;
    }

    /**
     * Move categories by ID (recursive move)
     *
     * @param cid           The categoryID we wish to move
     * @param newparent_id  The categoryID of the new parent category
     *
     * @return true or false
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
     * Move SubCategories by Path (recurisve move)
     *
     * @param apath         The path to move from
     * @param newparent_id  The categoryID of the new parent category
     * @param field         The field to use for the path reference (optional) (default='ipath')
     *
     * @return true or false
     */
    public static function moveSubCategoriesByPath($apath, $newparent_id, $field = 'ipath')
    {
        return self::moveCategoriesByPath($apath, $newparent_id, $field, false);
    }

    /**
     * Move Categories by Path (recursive move)
     *
     * @param apath         The path to move from
     * @param newparent_id  The categoryID of the new parent category
     * @param field         The field to use for the path reference (optional) (default='ipath')
     * @param includeRoot   whether or not to also move the root folder  (optional) (default=true)
     *
     * @return true or false
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

        // since array_shift() resets numeric array indexes, we remove the leading element like this
        if (!$includeRoot) {
            list ($k, $v) = each($cats);
            unset($cats[$k]);
        }

        $newParentIPath = $newParent['ipath'] . '/';
        $newParentPath = $newParent['path'] . '/';

        $oldParent = self::getCategoryByID($cats[0]['parent_id']);
        $oldParentIPath = $oldParent['ipath'] . '/';
        $oldParentPath = $oldParent['path'] . '/';

        ModUtil::dbInfoLoad('Categories');
        $pntables = System::dbGetTables();
        $category_table = $pntables['categories_category'];
        $category_column = $pntables['categories_category_column'];

        $pathField = $category_column[$field];
        $fpath = $category_column['path'];
        $fipath = $category_column['ipath'];

        $sql = "UPDATE $category_table SET
                $fpath = REPLACE($fpath, '$oldParentPath', '$newParentPath'),
                $fipath = REPLACE($fipath, '$oldParentIPath', '$newParentIPath')
                WHERE $pathField = '" . DataUtil::formatForStore($apath) . "' OR $pathField LIKE '" . DataUtil::formatForStore($apath) . "/%'";
        DBUtil::executeSQL($sql);

        $pid = $cats[0]['id'];
        $sql = "UPDATE $category_table SET $category_column[parent_id] = '" . DataUtil::formatForStore($newparent_id) . "' WHERE $category_column[id] = '" . DataUtil::formatForStore($pid) . "'";
        DBUtil::executeSQL($sql);

        return true;
    }

    /**
     * Copy categories by ID (recursive copy)
     *
     * @param cid           The categoryID we wish to copy
     * @param newparent_id  The categoryID of the new parent category
     *
     * @return true or false
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
     * Copy SubCategories by Path (recurisve copy)
     *
     * @param apath         The path to copy from
     * @param newparent_id  The categoryID of the new parent category
     * @param field         The field to use for the path reference (optional) (default='ipath')
     *
     * @return true or false
     */
    public static function copySubCategoriesByPath($apath, $newparent_id, $field = 'ipath')
    {
        return self::copyCategoriesByPath($apath, $newparent_id, $field, false);
    }

    /**
     * Copy Categories by Path (recurisve copy)
     *
     * @param apath         The path to copy from
     * @param newparent_id  The categoryID of the new parent category
     * @param field         The field to use for the path reference (optional) (default='ipath')
     * @param includeRoot   whether or not to also move the root folder (optional) (default=true)
     *
     * @return true or false
     */
    public static function copyCategoriesByPath($apath, $newparent_id, $field = 'ipath', $includeRoot = true)
    {
        if (!$apath) {
            return false;
        }

        $cats = self::getSubCategoriesByPath($apath, 'ipath', $field, true, true);
        $newParent = self::getCategoryByID($newparent_id);

        if (!$newParent || !$cats) {
            return false;
        }

        $oldToNewID = array();
        $oldToNewID[$cats[0]['parent_id']] = $newParent['id'];

        // since array_shift() resets numeric array indexes, we remove the leading element like this
        if (!$includeRoot) {
            list ($k, $v) = each($cats);
            unset($cats[$k]);
        }

        $ak = array_keys($cats);
        foreach ($ak as $v) {
            $cat = $cats[$v];

            $oldID = $cat['id'];
            $cat['id'] = '';
            $cat['parent_id'] = $oldToNewID[$cat['parent_id']];
            $cat['path'] = $newParent['path'] . '/' . $cat['path_relative'];

            $pnCat = new pnCategory($cat);
            $pnCat->insert();
            $oldToNewID[$oldID] = $pnCat->_objData['id'];
        }

        // rebuild iPath since now we have all new PathIDs
        self::rebuildPaths('ipath', 'id');
        return true;
    }

    /**
     * Check whether $cid is a direct subcategory of $root_id
     *
     * @param root_id    The root/parent ID
     * @param cid        The categoryID we wish to check for subcategory-ness.
     *
     * @return true or false
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
     * Check whether $cid is a direct subcategory of $root_id
     *
     * @param rootCat    The root/parent category
     * @param cat        The category we wish to check for subcategory-ness.
     *
     * @return true or false
     */
    public static function isDirectSubCategory($rootCat, $cat)
    {
        return $cat['parent_id'] == $rootCat['id'];
    }

    /**
     * Check whether $cid is a subcategory of $root_id
     *
     * @param root_id    The ID of the root category we wish to check from
     * @param cid        The category-id we wish to check for subcategory-ness.
     *
     * @return true or false
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
     * Check whether $cat is a subcategory of $rootCat
     *
     * @param rootCat    The root/parent category
     * @param cat        The category we wish to check for subcategory-ness.
     *
     * @return true or false
     */
    public static function isSubCategory($rootCat, $cat)
    {
        $rPath = $rootCat['ipath'] . '/';
        $cPath = $cat['ipath'];

        return strpos($cPath, $rPath) === 0;
    }

    /**
     * Check whether the category $cid has subcategories (optional checks for leafe )
     *
     * @param cid        The parent category
     * @param countOnly  whether or not to explicitly check for leaf nodes in the subcategories
     * @param all        whether or not to return all (or only active) subcategories
     *
     * @return true or false
     */
    public static function haveDirectSubcategories($cid, $countOnly = false, $all = true)
    {
        if (!$cid) {
            return false;
        }

        $cats = self::getCategoriesByParentID($cid, '', false, $all);

        if ($countOnly) {
            return (boolean) count($cats);
        }

        foreach ($cats as $cat) {
            if ($cat['is_leaf']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the java-script for the tree menu
     *
     * @param cats             The categories array to represent in the tree
     * @param doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true)
     *
     * @return generated tree JS text
     */
    public static function getCategoryTreeJS($cats, $doReplaceRootCat = true)
    {
        $menuString = self::getCategoryTreeStructure($cats);

        $treemid = new TreeMenu();
        $treemid->setMenuStructureString($menuString);
        $treemid->parseStructureForMenu('treemenu1');
        $treemid->setLibjsdir("javascript/phplayersmenu/libjs");
        $treemid->setImgdir("javascript/phplayersmenu/images");
        $treemid->setImgwww("javascript/phplayersmenu/images");
        $treemenu1 = $treemid->newTreeMenu('treemenu1');

        if ($doReplaceRootCat) {
            $treemenu1 = str_replace('__SYSTEM__', __('Root category'), $treemenu1);
        }

        return $treemenu1;
    }

    /**
     * insert one leaf in a category tree (path as keys) recursively
     *
     * $tree[name] = array of children
     * $tree[name]['_/_'] = branch/leaf data
     *
     * @return array Tree
     * @param $tree array Tree or branch
     * @param $entry array
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
     * make a list, sorted on each level, from a tree
     *
     * @return nothing
     * @param $tree array nested array from _tree_insert
     * @param $cats array list of categories (initially empty array)
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
                    $sorted[$k] = $v['_/_'][$_catSortField];
                }
                else {
                    $sorted[$k] = null;
                }
            }
        }
        asort($sorted);
        foreach ($sorted as $k => $v) {
            self::_tree_sort($tree[$k], $cats);
        }
    }

    /**
     * Take a raw list of category data, return it sorted on each level
     *
     * @return array list of categories, sorted on each level
     * @param $cats array list of categories (arrays)
     * @param $sortField string[optional] key of category arrays
     */
    public static function sortCategories($cats, $sortField = '')
    {
        if (!$cats)
            return $cats;

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
        return $new_cats;
    }

    /**
     * Return an array of folders the user has at least access/view rights to.
     *
     * @param $cats array  list of categories
     * @return array The resulting folder path array
     */
    public static function getCategoryTreeStructure($cats)
    {
        $menuString = '';
        $params = array();
        $params['mode'] = 'edit';

        Loader::loadClass('StringUtil');

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
     * Return the HTML selector code for the given category hierarchy
     *
     * @param cats              The category hierarchy to generate a HTML selector for
     * @param field             The field value to return (optional) (default='id')
     * @param selected          The selected category (optional) (default=0)
     * @param name              The name of the selector field to generate (optional) (default='category[parent_id]')
     * @param defaultValue      The default value to present to the user (optional) (default=0)
     * @param defaultText       The default text to present to the user (optional) (default='')
     * @param allValue          The value to assign to the "all" option (optional) (default=0)
     * @param allText           The text to assign to the "all" option (optional) (default='')
     * @param submit            whether or not to submit the form upon change (optional) (default=false)
     * @param displayPath       If false, the path is simulated, if true, the full path is shown (optional) (default=false)
     * @param doReplaceRootCat  Whether or not to replace the root category with a localized string (optional) (default=true)
     * @param multipleSize      If > 1, a multiple selector box is built, otherwise a normal/single selector box is build (optional) (default=1)
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
                (string) $selectedValue);
        }

        $id = strtr($name, '[]', '__');
        $multiple = $multipleSize > 1 ? ' multiple="multiple"' : '';
        $multipleSize = $multipleSize > 1 ? " size=\"$multipleSize\"" : '';
        $submit = $submit ? ' onchange="this.form.submit();"' : '';
        $lang = ZLanguage::getLanguageCode();

        $html = "<select name=\"$name\" id=\"$id\"{$multipleSize}{$multiple}{$submit}>";

        if (!empty($defaultText)) {
            $sel = (in_array((string) $defaultValue, $selectedValue) ? ' selected="selected"' : '');
            $html .= "<option value=\"$defaultValue\"$sel>$defaultText</option>";
        }

        if ($allText) {
            $sel = (in_array((string) $allValue, $selectedValue) ? ' selected="selected"' : '');
            $html .= "<option value=\"$allValue\"$sel>$allText</option>";
        }

        Loader::loadClass('StringUtil');
        $count = 0;
        if (!isset($cats) || empty($cats)) {
            $cats = array();
        }

        foreach ($cats as $cat) {
            if ($fieldIsAttribute) {
                $sel = (in_array((string) $cat['__ATTRIBUTES__'][$field], $selectedValue) ? ' selected="selected"' : '');
            } else {
                $sel = (in_array((string) $cat[$field], $selectedValue) ? ' selected="selected"' : '');
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
                if ($cslash > 0)
                    $indent = substr($line, 0, $cslash * 2);

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
     * Compare function for ML name field
     *
     * @param catA      1st category
     * @param catB      2nd category
     *
     * @return The resulting compare value
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
     * @param catA      1st category
     * @param catB      2nd category
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
     * Utility function to sort a category array by the current locale of
     * either the ML name or description
     *
     * @param cats      The categories array
     * @param func      Which compare function to use (determines field to be used for comparison) (optional) (defaylt='cmpName')
     *
     * @return The resulting sorted category array (original array altered in place)
     */
    public static function sortByLocale(&$cats, $func = 'cmpName')
    {
        usort($cats, $func);
        return;
    }

    /**
     * Resequence the sort fields for the given category
     *
     * @param cats      The categories array
     * @param step      The counting step/interval (optional) (default=1)
     *
     * @return True if something was done, false if an emtpy $cats was passed in
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
     * Given an array of categories (with the Property-Names being
     * the keys of the array) and it corresponding Parent categories (indexed
     * with the Property-Names too), return an (identically indexed) array
     * of category-paths based on the given field (name or id make sense)
     *
     * @param rootCatIDs    The root/parent categories ID
     * @param cats          The associative categories object array
     * @param includeRoot   If true, the root portion of the path is preserved
     *
     * @return The resulting folder path array (which is also altered in place)
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
     * Given a category with its parent category, return an (idenically indexed)
     * array of category-paths based on the given field (name or id make sense)
     *
     * @param rootCategory  The root/parent category
     * @param cat           The category to process
     * @param includeRoot   If true, the root portion of the path is preserved
     *
     * @return The resulting folder path array (which is also altered in place)
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
     * Given an array of categories (with the category-IDs being
     * the keys of the array), return an (idenically indexed) array
     * of category-paths based on the given field (name or id make sense)
     *
     * @param cats      The associative categories object array
     * @param field     Which field to use the building the path (optional) (default='name')
     *
     * @return The resulting folder path array
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
     * Rebuild the path field for all categories in the database
     * Note that the
     *
     * @param field         The field which we wish to populate (optional) (default='path')
     * @param sourceField   The field we use to build the path with (optional) (default='name')
     * @param leaf_id       The leaf-category category-id (ie: we'll rebuild the path of this category and all it's parents) (optional) (default=0)
     *
     * Note that field and sourceField go in pairs (that is, if you want sensical results)!
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
     * Check for access to a certain set of categories
     *
     * For each category property in the list, check if we have access to that category in that property.
     * Check is done as "Categories:Property:$propertyName", "$cat[id]::"
     *
     * @param array $categories Array of category data (as returned from ObjectUtil::expandObjectWithCategories).
     * @param int   $permLevel Required permision level.
     *
     * @return bool True if access is allowed to at least one of the categories
     */
    public static function hasCategoryAccess($categories, $module, $permLevel = ACCESS_OVERVIEW)
    {
        // Always allow access to content with no categories associated
        if (count($categories) == 0)
            return true;

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

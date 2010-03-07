<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * CategoryRegistryUtil
 *
 * @package Zikula_Core
 * @subpackage CategoryRegistryUtil
 */
class CategoryRegistryUtil
{
    /**
     * Register a module category
     *
     * @param catreg    The array of category map data objects
     *
     * @return boolean The DB insert operation result code cast to a boolean
     */
    public static function registerModuleCategory($catreg)
    {
        if (!$catreg)
            return false;

        if (!pnModDBInfoLoad('Categories')) {
            return false;
        }

        if ($catreg['id']) {
            $res = DBUtil::updateObject($catreg, 'categories_registry');
        } else {
            $res = DBUtil::insertObject($catreg, 'categories_registry');
        }

        return (boolean) $res;
    }

    /**
     * Register module categories
     *
     * @param catregs    The array of category map data objects
     *
     * @return true
     */
    public static function registerModuleCategories($catregs)
    {
        if (!$catregs) {
            return false;
        }

        if (!pnModDBInfoLoad('Categories')) {
            return false;
        }

        foreach ($catregs as $catreg) {
            if ($catreg['id']) {
                $res = DBUtil::updateObject($catreg, 'categories_registry');
            } else {
                $res = DBUtil::insertObject($catreg, 'categories_registry');
            }
        }

        return true;
    }

    /**
     * Get registered Categories for a module
     *
     * @param modname    The module name
     * @param tablename  The tablename for which we wish to get the property for
     *
     * @return The associative field array of registered categories for the specified module
     */
    public static function getRegisteredModuleCategories($modname, $tablename)
    {
        if (!$modname || !$tablename) {
            return pn_exit(__("Error! Received invalid specifications '%s', '%s'.", array($modname, $tablename)));
        }

        if (!pnModDBInfoLoad('Categories')) {
            return false;
        }

        static $cache = array();
        if (isset($cache[$modname][$tablename])) {
            return $cache[$modname][$tablename];
        }

        $wheres = array();
        $pntables = pnDBGetTables();
        $col = $pntables['categories_registry_column'];
        $wheres[] = "$col[modname]='" . DataUtil::formatForStore($modname) . "'";
        $wheres[] = "$col[table]='" . DataUtil::formatForStore($tablename) . "'";
        $where = implode(' AND ', $wheres);
        $sort = "$col[id] ASC";
        $fArr = DBUtil::selectFieldArray('categories_registry', 'category_id', $where, $sort, false, 'property');

        $cache[$modname][$tablename] = $fArr;
        return $fArr;
    }

    /**
     * Get registered category for module property
     *
     * @param modname    The module we wish to get the property for
     * @param tablename  The tablename for which we wish to get the property for
     * @param property   The property name
     * @param default    The default value to return if the requested value is not set (optional) (default=null)
     *
     * @return The associative field array of registered categories for the specified module
     */
    public static function getRegisteredModuleCategory($modname, $tablename, $property, $default = null)
    {
        if (!$modname || !$property) {
            return $default;
        }

        $fArr = self::getRegisteredModuleCategories($modname, $tablename);

        if ($fArr && isset($fArr[$property]) && $fArr[$property]) {
            return $fArr[$property];
        }

        // if we have a path default, we get the ID
        if ($default && !is_integer($default)) {
            if (!Loader::loadClass('CategoryUtil')) {
                return pn_exit(__f('Error! Unable to load class [%s]', 'CategoryUtil'));
            }

            $cat = CategoryUtil::getCategoryByPath($default);
            if ($cat) {
                $default = $cat['id'];
            }
        }

        return $default;
    }

    /**
     * Get the IDs of the property registers
     *
     * @param modname    The module name
     * @param tablename  The tablename for which we wish to get the property for
     *
     * @return The associative field array of register ids for the specified module
     */
    public static function getRegisteredModuleCategoriesIds($modname, $tablename)
    {
        if (!$modname || !$tablename) {
            return pn_exit(__f("Error! Received invalid specifications '%s', '%s'.", array($modname, $tablename)));
        }

        if (!pnModDBInfoLoad('Categories')) {
            return false;
        }

        $wheres = array();
        $pntables = pnDBGetTables();
        $col = $pntables['categories_registry_column'];
        $wheres[] = "$col[modname]='" . DataUtil::formatForStore($modname) . "'";
        $wheres[] = "$col[table]='" . DataUtil::formatForStore($tablename) . "'";
        $where = implode(' AND ', $wheres);
        $fArr = DBUtil::selectFieldArray('categories_registry', 'id', $where, '', false, 'property');

        return $fArr;
    }
}

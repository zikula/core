<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Admin_Api_Admin extends Zikula_AbstractApi
{
    /**
     * create a admin category
     * @param string $args['catname'] name of the category
     * @param string $args['description'] description of the category
     * @return mixed admin category ID on success, false on failure
     */
    public function create($args)
    {
        // Argument check
        if (!isset($args['catname']) ||
                !strlen($args['catname']) ||
                !isset($args['description'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (!SecurityUtil::checkPermission('Admin::Category', "$args[catname]::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError ();
        }

        $count = $categories = ModUtil::apiFunc('Admin', 'admin', 'countitems');
        $category = array('catname' => $args['catname'], 'description' => $args['description'], 'order' => $count);

        if (!DBUtil::insertObject($category, 'admin_category', 'cid')) {
            return LogUtil::registerError($this->__('Error! Could not create the new item.'));
        }

        // Return the id of the newly created item to the calling process
        return $category['cid'];
    }

    /**
     * delete a admin category
     * @param int $args['cid'] ID of the category
     * @return bool true on success, false on failure
     */
    public function delete($args)
    {
        if (!isset($args['cid']) || !is_numeric($args['cid'])) {
            return LogUtil::registerArgsError();
        }

        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $args['cid']));

        if ($category == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$category[cid]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError ();
        }

        // Avoid deletion of the default category
        $defaultcategory = $this->getVar('defaultcategory');
        if ($category['cid'] == $defaultcategory) {
            return LogUtil::registerError($this->__('Error! You cannot delete the default module category used in the administration panel.'));
        }

        // Avoid deletion of the start category
        $startcategory = $this->getVar('startcategory');
        if ($category['cid'] == $startcategory) {
            return LogUtil::registerError($this->__('Error! This module category is currently set as the category that is initially displayed when you visit the administration panel. You must first select a different category for initial display. Afterwards, you will be able to delete the category you have just attempted to remove.'));
        }

        // move all modules from the category to be deleted into the
        // default category. We can't do this via a simple DBUtil call
        // because it's a non-object based mass update of the key field.
        $dbtable = DBUtil::getTables();
        $column  = $dbtable['admin_module_column'];
        $where   = "WHERE $column[cid] = '" . (int)DataUtil::formatForStore($category['cid']) . "'";

        $obj = array();
        $obj['cid'] = $defaultcategory;
        $res = DBUtil::updateObject ($obj, 'admin_module', $where);
        if (!$res) {
            return LogUtil::registerError($this->__('Error! Could not perform the deletion.'));
        }

        // Now actually delete the category
        if (!DBUtil::deleteObjectByID ('admin_category', $category['cid'], 'cid')) {
            return LogUtil::registerError($this->__('Error! Could not perform the deletion.'));
        }

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * update a admin category
     * @param int $args['cid'] the ID of the category
     * @param string $args['catname'] the new name of the category
     * @param string $args['description'] the new description of the category
     * @return bool true on success, false on failure
     */
    public function update($args)
    {
        // Argument check
        if (!isset($args['cid']) ||
                !is_numeric($args['cid']) ||
                !isset($args['catname']) ||
                !strlen($args['catname']) ||
                !isset($args['description'])) {
            return LogUtil::registerArgsError();
        }

        // Get the existing item
        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $args['cid']));

        if ($category == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security checks (both old item and updated item)
        if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$args[cid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError ();
        }
        if (!SecurityUtil::checkPermission('Admin::Category', "$args[catname]:$args[cid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError ();
        }

        $category = array('cid' => $args['cid'], 'catname' => $args['catname'], 'description' => $args['description']);

        if (!DBUtil::updateObject($category, 'admin_category', '', 'cid')) {
            return LogUtil::registerError($this->__('Error! Could not save your changes.'));
        }

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * get all admin categories
     * @param int $args['startnum'] starting record number
     * @param int $args['numitems'] number of items to get
     * @return mixed array of items, or false on failure
     */
    public function getall($args)
    {
        // Optional arguments.
        if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
            $args['startnum'] = 1;
        }
        if (!isset($args['numitems']) || !is_numeric($args['numitems'])) {
            $args['numitems'] = -1;
        }

        // argument check
        if (!isset($args['startnum']) ||
                !isset($args['numitems'])) {
            return LogUtil::registerArgsError();
        }

        $categories = array();

        // Security check
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_READ)) {
            return $categories;
        }

        // get the necessary db information
        ModUtil::dbInfoLoad('Admin', 'Admin');
        $dbtable = DBUtil::getTables();
        $admincategorycolumn = &$dbtable['admin_category_column'];

        // get all categories the user has permission to see
        $orderBy = "ORDER BY $admincategorycolumn[order]";
        $permFilter = array(array('realm'          => 0,
                        'component_left' => 'Admin',
                        'instance_left'  => 'catname',
                        'instance_right' => 'cid',
                        'level'          => ACCESS_READ));
        $categories = DBUtil::selectObjectArray('admin_category', '', $orderBy, $args['startnum']-1, $args['numitems'], '', $permFilter);
        if (!$categories) {
            return false;
        }

        return $categories;
    }

    /**
     * get a specific category
     * @param int $args['cid'] id of example item to get
     * @return mixed item array, or false on failure
     */
    public function get($args)
    {
        // Argument check
        if (!isset($args['cid'])) {
            return LogUtil::registerArgsError();
        }

        // retrieve the category object
        $category = DBUtil::selectObjectByID('admin_category', (int)$args['cid'], 'cid');
        if (!$category) {
            return false;
        }

        if (!SecurityUtil::checkPermission('Admin::', "$category[catname]::$category[cid]", ACCESS_READ)) {
            return LogUtil::registerPermissionError ();
        }

        // Return the item array
        return $category;
    }

    /**
     * utility function to count the number of items held by this module
     * @return int number of items held by this module
     */
    public function countitems()
    {
        return DBUtil::selectObjectCount('admin_category');
    }

    /**
     * add a module to a category
     * @param string $args['module'] name of the module
     * @param int $args['category'] number of the category
     * @return mixed admin category ID on success, false on failure
     */
    public function addmodtocategory($args)
    {
        if (!isset($args['module']) ||
                !isset($args['category'])) {
            return LogUtil::registerArgsError();
        }

        // this function is called durung the init process so we have to check in installing
        // is set as alternative to the correct permission check
        if (!System::isInstalling() && !SecurityUtil::checkPermission('Admin::Category', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError ();
        }

        // get module id
        $mid = ModUtil::getIdFromName($args['module']);
        if (!DBUtil::deleteObjectByID ('admin_module', $mid, 'mid')) {
            return false;
        }

        $values = array();
        $values['cid'] = $args['category'];
        $values['mid'] = $mid;

        $values['order'] = ModUtil::apiFunc('Admin', 'admin', 'countModsInCat', array('cid' =>$args['category']));

        if (!DBUtil::insertObject($values, 'admin_module')) {
            return false;
        }

        // Return success
        return true;
    }

    /**
     * Get the category a module belongs to
     * @param int $args['mid'] id of the module
     * @return mixed category id, or false on failure
     */
    public function getmodcategory($args)
    {
        // create a static result set to prevent multiple sql queries
        static $catitems = array();

        // Argument check
        if (!isset($args['mid'])) {
            return LogUtil::registerArgsError();
        }

        // check if we've already worked this query out
        if (isset($catitems[$args['mid']])) {
            return $catitems[$args['mid']];
        }

        // retrieve the admin module object array
        $result = DBUtil::selectObjectArray('admin_module', '', '', -1, -1, 'mid');
        if (!$result) {
            return false;
        }

        $ak = array_keys($result);
        foreach ($ak as $val) {
            $catitems[$val] = $result[$val]['cid'];
        }

        // Return the category id
        if (isset($catitems[$args['mid']])) {
            return $catitems[$args['mid']];
        }

        return false;
    }

    /**
     * Get the category a module belongs to
     * @param int $args['mid'] id of the module
     * @return mixed category id, or false on failure
     */
    public function getSortOrder($args)
    {

        // Argument check
        if (!isset($args['mid'])) {
            return LogUtil::registerArgsError();
        }

        // retrieve the admin module object array
        //$result = DBUtil::selectObject('admin_module', );
        $result = DBUtil::selectObjectByID('admin_module', (int)$args['mid'], 'mid');
        if (!$result) {
            return false;
        }
        return $result['order'];

    }




    /**
     * Get the category a module belongs to
     * @return array of categories
     */
    public function getmodcategories($args)
    {

        $joinInfo = array();
        $joinInfo[] = array ( 'join_table'          =>  'admin_category',
                'join_field'          =>  'catname',
                'object_field_name'   =>  'category_name',
                'compare_field_table' =>  'cid',
                'compare_field_join'  =>  'cid');


        // retrieve the admin module object array
        $result = DBUtil::selectExpandedObjectArray('admin_module', $joinInfo, '', '', -1, -1, 'mid');
        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * Get the category a module belongs to
     * @param int $args['mid'] id of the module
     * @return mixed array of styles if successful, or false on failure
     */
    public function getmodstyles($args)
    {
        // check our input and get the module information
        if (!isset($args['modname']) ||
                !is_string($args['modname']) ||
                !is_array($modinfo = ModUtil::getInfoFromName($args['modname']))) {
            return LogUtil::registerArgsError();
        }

        if (!isset($args['exclude']) || !is_array($args['exclude'])) {
            $args['exclude'] = array();
        }

        // create an empty result set
        $styles = array();

        $osmoddir = DataUtil::formatForOS($modinfo['directory']);
        $base = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        if (is_dir($dir = "$base/$osmoddir/style") || is_dir($dir = "$base/$osmoddir/pnstyle")) {
            $handle = opendir($dir);
            while (false !== ($file = readdir($handle))) {
                if (stristr($file, '.css') && !in_array($file, $args['exclude'])) {
                    $styles[] = $file;
                }
            }
        }

        // return our results
        return $styles;
    }

    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Admin::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('Admin', 'admin', 'view'), 'text' => $this->__('Module categories list'), 'class' => 'z-icon-es-view');
        }
        if (SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Admin', 'admin', 'newcat'), 'text' => $this->__('Create new module category'), 'class' => 'z-icon-es-new');
        }
        if (SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Admin', 'admin', 'help'), 'text' => $this->__('Help'), 'class' => 'z-icon-es-help');
            $links[] = array('url' => ModUtil::url('Admin', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
        }

        return $links;
    }

    public function countModsInCat($args)
    {
        if (!isset($args['cid'])) {
            return LogUtil::registerArgsError();
        }

        return DBUtil::selectObjectCountByID('admin_module', $args['cid'], 'cid');
    }

    /**
     * Open the admin container
     */
    public function adminheader()
    {
        $view = Zikula_View::getInstance('Admin');
        return $view->fetch('admin_admin_header.tpl');
    }

    /**
     * Close the admin container
     */
    public function adminfooter()
    {
        $view = Zikula_View::getInstance('Admin');
        return $view->fetch('admin_admin_footer.tpl');
    }
}
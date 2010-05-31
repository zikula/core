<?php
/**
 * Copyright 2009 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 * @link http://www.zikula.org
 * @version $Id$
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Admin_Ajax extends AbstractController
{
    /**
     * Change the category a module belongs to by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          alerttext is a string empty if no problems.
     *                          response is a string -1 on failure moduleid on sucess.
     */
    public function changeModuleCategory() {

        //security checks
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            $output['alerttext'] = 'You do not have permission to do this.';
            return AjaxUtil::output($output, true);
        }
        if (!SecurityUtil::confirmAuthKey()) {
            $output['alerttext'] = "Invalid AuthKey.";
            return AjaxUtil::output($output, false);
        }

        //get passed information
        $moduleID = FormUtil::getPassedValue("modid");
        $newParentCat = FormUtil::getPassedValue("cat");

        //get info on the module
        $module = ModUtil::getInfo($moduleID);
        if (!$module) {
            //deal with couldnt get module info
            $output['alerttext'] = "Could not get module name for id:$moduleID";
            return AjaxUtil::output($output, true);
        }
        //get the module name
        $module = $module['name'];
        //move the module
        $result = ModUtil::apiFunc('Admin', 'admin', 'addmodtocategory', array('category' => $newParentCat,
                'module' => $module));
        $output['alerttext'] = '';
        $output['response'] = ($result) ? $moduleID : "-1";
        return AjaxUtil::output($output, true);
    }

    /**
     * Add a new admin category by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          alerttext is a string empty if no problems.
     *                          response is a string 0 on failure new cid on sucess.
     *                          url is a formatted url to the new category on success.
     */
    public function addCategory() {

        //security checks
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            $output['alerttext'] = 'You do not have permission to do this.';
            return AjaxUtil::output($output, true);
        }
        if (!SecurityUtil::confirmAuthKey()) {
            $output['alerttext'] = "Invalid AuthKey.";
            return AjaxUtil::output($output, false);
        }

        //get form information
        $catName = trim(FormUtil::getPassedValue('catname'));
        //TODO make sure catName is set.

        //check if there exists a cat with this name.
        $cats = ModUtil::apiFunc('Admin', 'admin', 'getall');
        foreach ($cats as $cat) {
            if (in_array($catName, $cat)) {
                $output['alerttext'] = 'A category by this name already exists.';
                return AjaxUtil::output($output, true);
            }
        }
        //create the category
        $result = ModUtil::apiFunc('Admin', 'admin', 'create', array('catname' => $catName,
                'description' => ''));
        $output['alerttext'] = '';
        $output['response'] = (!$result) ? "0" : $result;
        $url = ModUtil::url('Admin', 'admin', 'adminpanel', array('acid' => $result));
        $output['url'] = $url;
        AjaxUtil::output($output, true);
    }

    /**
     * Delete an admin category by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          alerttext is a string empty if no problems.
     *                          response is a string -1 on failure deleted cid on sucess.
     */
    public function deleteCategory() {
        //security checks
        if (!SecurityUtil::confirmAuthKey()) {
            $output['alerttext'] = "Invalid AuthKey.";
            $output['response'] = '-1';
            return AjaxUtil::output($output, false);
        }
        //get passed cid to delete
        $cid = trim(FormUtil::getPassedValue('cid'));
        //check user has permission to delete this
        if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$cid", ACCESS_DELETE)) {
            $output['alerttext'] = 'You do not have permission to delete category:'.$cid;
            $output['response'] = '-1';
            return AjaxUtil::output($output, true);
        }
        //find the category corrisponding to the cid.
        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));
        if ($category == false) {
            $output['alerttext'] = 'Could not find category:'.$cid;
            $output['response'] = '-1';
            return AjaxUtil::output($output, true);
        }

        //delete the category
        if (ModUtil::apiFunc('Admin', 'admin', 'delete', array('cid' => $cid))) {
            // Success
            $output['alerttext'] = '';
            $output['response'] = $cid;
            return AjaxUtil::output($output, true);
        }
        //unknown error
        $output['alerttext'] = 'Unknown error.';
        $output['response'] = '-1';
        return AjaxUtil::output($output, true);
    }

    /**
     * Edit an admin category by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     */
    public function editCategory() {
        //get form values
        $cid = trim(FormUtil::getPassedValue('cid'));
        $cat = trim(FormUtil::getPassedValue('catname'));

        //security checks
        if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$cid", ACCESS_EDIT)) {
            $output['alerttext'] = 'You do not have permission to edit this category.';
            $output['response'] = '-1';
            return AjaxUtil::output($output, true);
        }
        if (!SecurityUtil::confirmAuthKey()) {
            $output['alerttext'] = 'Invalid AuthKey';
            $output['response'] = '-1';
            return AjaxUtil::output($output, false);
        }

        //make sure cid and category name (cat) are both set
        if (!isset($cid) || $cid == '' || !isset($cat) || $cat == '') {
            $output['alerttext'] = 'ID or Cateogry name not set.';
            $output['response'] = '-1';
            return AjaxUtil::output($output, true);
        }

        //check if category with same name exists
        $cats = ModUtil::apiFunc('Admin', 'admin', 'getall');
        foreach ($cats as $catName) {
            if (in_array($cat, $catName)) {
                //check to see if the category with same name is the same category.
                if ($catName['cid'] == $cid) {
                    $output['alerttext'] = '';
                    $output['response'] = $cat;
                    return AjaxUtil::output($output, true);
                }
                //a different category has the same name, not allowed.
                $output['alerttext'] = 'A category by this name already exists.';
                $output['response'] = '-1';
                return AjaxUtil::output($output, true);
            }
        }

        //get the category from the database
        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));
        if ($category == false) {
            $output['alerttext'] = "Category $cid does not exist.";
            $output['response'] = '-1';
            return AjaxUtil::output($output, true);
        }

        //update the category using the info from the database and from the form.
        if (ModUtil::apiFunc('Admin', 'admin', 'update', array('cid' => $cid, 'catname' => $cat, 'description' => $category['description']))) {
            $output['alerttext'] = '';
            $output['response'] = $cat;
            return AjaxUtil::output($output, true);
        }
        //update failed for some reason
        $output['alerttext'] = 'Unknown error.';
        $output['response'] = '-1';
        return AjaxUtil::output($output, true);
    }
}
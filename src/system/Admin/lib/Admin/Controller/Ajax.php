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

class Admin_Controller_Ajax extends Zikula_Controller
{
    public function _postSetup()
    {
        // no need for a Zikula_View so override it.
    }

    /**
     * Change the category a module belongs to by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          response is a string moduleid on sucess.
     */
    public function changeModuleCategory() {

        //security checks
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true));
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError());
        }

        //get passed information
        $moduleID = FormUtil::getPassedValue("modid");
        $newParentCat = FormUtil::getPassedValue("cat");

        //get info on the module
        $module = ModUtil::getInfo($moduleID);
        if (!$module) {
        	//deal with couldnt get module info
        	return AjaxUtil::error($this->__f('Error! Could not get module name for id %s.', DataUtil::formatForDisplay($moduleID)), array(), true);
        }
        //get the module name
        $displayname = DataUtil::formatForDisplay($module['displayname']);
        $module = $module['name'];
        $oldcid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory', array('mid' => $moduleID));
        //move the module
        $result = ModUtil::apiFunc('Admin', 'admin', 'addmodtocategory', array('category' => $newParentCat,
                'module' => $module));
        if(!$result) {
            return AjaxUtil::error(LogUtil::registerError($this->__('Error! Could not add module to module category.')));
        }
        $output['response'] = $moduleID;
        $output['newParentCat'] = $newParentCat;
        $output['oldcid'] = $oldcid;
        $output['modulename'] = $displayname;
        $output['url'] = ModUtil::url($module, 'admin');
        return AjaxUtil::output($output, true);
    }

    /**
     * Add a new admin category by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          response is a string the new cid on sucess.
     *                          url is a formatted url to the new category on success.
     */
    public function addCategory() {

        //security checks
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true));
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError());
        }

        //get form information
        $catName = trim(FormUtil::getPassedValue('catname'));
        //TODO make sure catName is set.

        //check if there exists a cat with this name.
        $cats = ModUtil::apiFunc('Admin', 'admin', 'getall');
        foreach ($cats as $cat) {
            if (in_array($catName, $cat)) {
            	return AjaxUtil::error($this->__('Error! A category by this name already exists.'), array(), true);
            }
        }
        //create the category
        $result = ModUtil::apiFunc('Admin', 'admin', 'create', array('catname' => $catName,
                'description' => ''));
        if (!$result) {
            return AjaxUtil::error($this->__('The category could not be created.'), array(), true);
        }
        $output['response'] = $result;
        $url = ModUtil::url('Admin', 'admin', 'adminpanel', array('acid' => $result));
        $output['url'] = $url;
        AjaxUtil::output($output, true);
    }

    /**
     * Delete an admin category by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          response is a string cid on sucess.
     */
    public function deleteCategory() {
        //security checks
        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError(), array(), false, true, '403 Unauthorized');
        }
        //get passed cid to delete
        $cid = trim(FormUtil::getPassedValue('cid'));
        //check user has permission to delete this
        if (!SecurityUtil::checkPermission('Admin::Category', "::$cid", ACCESS_DELETE)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true), array(), false, true, '403 Unauthorized');
        }
        //find the category corrisponding to the cid.
        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));
        if ($category == false) {
            return AjaxUtil::error(LogUtil::registerError($this->__('Error! No such category found.')),array(), true);
        }

        //delete the category
        if (ModUtil::apiFunc('Admin', 'admin', 'delete', array('cid' => $cid))) {
            // Success
            $output['response'] = $cid;
            return AjaxUtil::output($output, true);
        }
        //unknown error
        return AjaxUtil::error(LogUtil::registerError($this->__('Error! Could not perform the deletion.')), array(), true);
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
        if (!SecurityUtil::checkPermission('Admin::Category', "$cat[catname]::$cid", ACCESS_EDIT)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true), array(), false, true, '403 Unauthorized');
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError(), array(), false, true, '403 Unauthorized');
        }

        //make sure cid and category name (cat) are both set
        if (!isset($cid) || $cid == '' || !isset($cat) || $cat == '') {
            return AjaxUtil::error(LogUtil::registerArgsError(), array(), true);
        }

        //check if category with same name exists
        $cats = ModUtil::apiFunc('Admin', 'admin', 'getall');
        foreach ($cats as $catName) {
            if (in_array($cat, $catName)) {
                //check to see if the category with same name is the same category.
                if ($catName['cid'] == $cid) {
                    $output['response'] = $cat;
                    return AjaxUtil::output($output, true);
                }
                //a different category has the same name, not allowed.
                return AjaxUtil::error($this->__('Error! A category by this name already exists.'), array(), true);
            }
        }

        //get the category from the database
        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));
        if ($category == false) {
            return AjaxUtil::error(LogUtil::registerError($this->__('Error! No such category found.')), array(), true);
        }

        //update the category using the info from the database and from the form.
        if (ModUtil::apiFunc('Admin', 'admin', 'update', array('cid' => $cid, 'catname' => $cat, 'description' => $category['description']))) {
            $output['response'] = $cat;
            return AjaxUtil::output($output, true);
        }
        //update failed for some reason
        return AjaxUtil::error(LogUtil::registerError($this->__('Error! Could not save your changes.')), array(), true);
    }
}
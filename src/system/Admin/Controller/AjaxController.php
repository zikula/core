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

class Admin_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * Change the category a module belongs to by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          response is a string moduleid on sucess.
     */
    public function changeModuleCategoryAction()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN));

        $moduleID = $this->request->getPost()->get('modid');
        $newParentCat = $this->request->getPost()->get('cat');

        //get info on the module
        $module = ModUtil::getInfo($moduleID);
        if (!$module) {
            //deal with couldnt get module info
            throw new Zikula_Exception_Fatal($this->__('Error! Could not get module name for id %s.'));
        }
        //get the module name
        $displayname = DataUtil::formatForDisplay($module['displayname']);
        $module = $module['name'];
        $oldcid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory', array('mid' => $moduleID));
        //move the module
        $result = ModUtil::apiFunc('Admin', 'admin', 'addmodtocategory', array('category' => $newParentCat,
                'module' => $module));
        if(!$result) {
            throw new Zikula_Exception_Fatal($this->__('Error! Could not add module to module category.'));
        }
        $output = array();
        $output['response'] = $moduleID;
        $output['newParentCat'] = $newParentCat;
        $output['oldcid'] = $oldcid;
        $output['modulename'] = $displayname;
        $output['url'] = ModUtil::url($module, 'admin', 'main');
        return new Zikula_Response_Ajax($output);
    }

    /**
     * Add a new admin category by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          response is a string the new cid on sucess.
     *                          url is a formatted url to the new category on success.
     */
    public function addCategoryAction()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN));

        //get form information
        $catName = trim($this->request->getPost()->get('catname'));

        //TODO make sure catName is set.

        //check if there exists a cat with this name.
        $cats = ModUtil::apiFunc('Admin', 'admin', 'getall');
        foreach ($cats as $cat) {
            if (in_array($catName, $cat)) {
                throw new Zikula_Exception_Fatal($this->__('Error! A category by this name already exists.'));
            }
        }
        //create the category
        $result = ModUtil::apiFunc('Admin', 'admin', 'create', array('catname' => $catName,
                'description' => ''));
        if (!$result) {
            throw new Zikula_Exception_Fatal($this->__('The category could not be created.'));
        }
        $output = array();
        $output['response'] = $result;
        $url = ModUtil::url('Admin', 'admin', 'adminpanel', array('acid' => $result));
        $output['url'] = $url;
        return new Zikula_Response_Ajax($output);
    }

    /**
     * Delete an admin category by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          response is a string cid on success.
     */
    public function deleteCategoryAction()
    {
        $this->checkAjaxToken();

        //get passed cid to delete
        $cid = trim($this->request->getPost()->get('cid'));
        //check user has permission to delete this
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Admin::Category', "::$cid", ACCESS_DELETE));

        //find the category corresponding to the cid.
        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));
        if ($category == false) {
            throw new Zikula_Exception_Fatal($this->__('Error! No such category found.'));
        }

        $output = array();
        //delete the category
        if (ModUtil::apiFunc('Admin', 'admin', 'delete', array('cid' => $cid))) {
            // Success
            $output['response'] = $cid;
            return new Zikula_Response_Ajax($output);
        }
        //unknown error
        throw new Zikula_Exception_Fatal($this->__('Error! Could not perform the deletion.'));
    }

    /**
     * Edit an admin category by ajax.
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     */
    public function editCategoryAction()
    {
        $this->checkAjaxToken();
        //get form values
        $cid = trim($this->request->getPost()->get('cid'));
        $cat = trim($this->request->getPost()->get('catname'));

        //security checks
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Admin::Category', "$cat[catname]::$cid", ACCESS_EDIT));

        //make sure cid and category name (cat) are both set
        if (!isset($cid) || $cid == '' || !isset($cat) || $cat == '') {
            throw new Zikula_Exception_BadData($this->__('No category name or id set.'));
        }

        $output = array();
        //check if category with same name exists
        $cats = ModUtil::apiFunc('Admin', 'admin', 'getall');
        foreach ($cats as $catName) {
            if (in_array($cat, $catName)) {
                //check to see if the category with same name is the same category.
                if ($catName['cid'] == $cid) {
                    $output['response'] = $cat;
                    return new Zikula_Response_Ajax($output);
                }
                //a different category has the same name, not allowed.
                throw new Zikula_Exception_Fatal($this->__('Error! A category by this name already exists.'));
            }
        }

        //get the category from the database
        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));
        if ($category == false) {
            throw new Zikula_Exception_Fatal($this->__('Error! No such category found.'));
        }

        //update the category using the info from the database and from the form.
        if (ModUtil::apiFunc('Admin', 'admin', 'update', array('cid' => $cid, 'catname' => $cat, 'description' => $category['description']))) {
            $output['response'] = $cat;
            return new Zikula_Response_Ajax($output);
        }
        //update failed for some reason
        throw new Zikula_Exception_Fatal($this->__('Error! Could not save your changes.'));
    }

    /**
     * Make a category the initially selected one (by ajax).
     *
     * @return AjaxUtil::output Output to the calling ajax request is returned.
     *                          response is a string message on success.
     */
    public function defaultCategoryAction()
    {
        $this->checkAjaxToken();
        //check user has permission to change the initially selected category
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN));

        //get passed cid
        $cid = trim($this->request->getPost()->get('cid'));
        //find the category corresponding to the cid.
        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));
        if ($category == false) {
            return AjaxUtil::error(LogUtil::registerError($this->__('Error! No such category found.')),array(), true);
        }

        $output = array();
        //make category the initially selected one
        if (ModUtil::setVar('Admin', 'startcategory', $cid)) {
            // Success
            $output['response'] = $this->__f('Category "%s" was successfully made default.', $category['catname']);
            return new Zikula_Response_Ajax($output);
        }
        //unknown error
        throw new Zikula_Exception_Fatal($this->__('Error! Could not make this category default.'));
    }
    
    public function sortCategoriesAction()
    {
        $this->checkAjaxToken();

        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN));

        $data = $this->request->getPost()->get('admintabs');

        $objects = array();
        foreach ($data as $order => $id) {
            array_push($objects, array("cid" => $id, "order" => $order));
        }
        DBUtil::updateObjectArray($objects,'admin_category','cid');
        return new Zikula_Response_Ajax(array());
    }
    
    
     public function sortModulesAction()
    {
        $this->checkAjaxToken();

        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN));

        $data = $this->request->getPost()->get('modules');

        $objects = array();
        foreach ($data as $order => $id) {
            array_push($objects, array("mid" => $id, "order" => $order));
        }
        DBUtil::updateObjectArray($objects,'admin_module','mid');
        return new Zikula_Response_Ajax(array());
    }
}

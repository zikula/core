<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @copyright Zikula Foundation
 * @package Zikula
 * @subpackage ZikulaAdminModule
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\AdminModule\Controller;

use SecurityUtil;
use ModUtil;
use DataUtil;
use Zikula_Response_Ajax;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * Ajax controllers for the admin module
 *
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * Change the category a module belongs to by ajax.
     *
     * @return \Zikula_Response_Ajax Ajax response containg the moduleid on success.
     *
     * @throws \FatalErrorException Thrown if the supplied module ID doesn't exist or
     *                                     if the module couldn't be added to the category
     */
    public function changeModuleCategoryAction()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN));

        $moduleID = $this->request->request->get('modid');
        $newParentCat = (int)$this->request->request->get('cat');

        //get info on the module
        $module = ModUtil::getInfo($moduleID);
        if (!$module) {
            //deal with couldn't get module info
            throw new FatalErrorException($this->__('Error! Could not get module name for id %s.'));
        }

        //get the module name
        $displayname = DataUtil::formatForDisplay($module['displayname']);
        $module = $module['name'];
        $oldcid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory', array('mid' => $moduleID));

        //move the module
        $result = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', array('category' => $newParentCat, 'module' => $module));
        if (!$result) {
            throw new FatalErrorException($this->__('Error! Could not add module to module category.'));
        }

        $output = array(
            'id' => $moduleID,
            'name' => $displayname,
            'url' => ModUtil::url($module, 'admin', 'index'),
            'parentCategory' => $newParentCat,
            'oldCategory' => $oldcid,
        );

        return new Zikula_Response_Ajax($output);
    }

    /**
     * Add a new admin category by ajax.
     *
     * @return \Zikula_Response_Ajax Ajax response containing the new cid on sucess
     *
     * @throws \FatalErrorException Thrown if the supplied category name already exists or
     *                                     if the the category couldn't be created
     */
    public function addCategoryAction()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN));

        //get form information
        $name = trim($this->request->request->get('name'));

        //TODO make sure name is set.

        //check if there exists a cat with this name.
        $cats = array();
        $items = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        foreach ($items as $item) {
            if (SecurityUtil::checkPermission('ZikulaAdminModule::', "$item[name]::$item[cid]", ACCESS_READ)) {
                $cats[] = $item;
            }
        }

        foreach ($cats as $cat) {
            if ($name == $cat['name']) {
                throw new FatalErrorException($this->__('Error! A category by this name already exists.'));
            }
        }

        // Security check
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::Category', "$name::", ACCESS_ADD));

        //create the category
        $result = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'create', array('name' => $name, 'description' => ''));
        if (!$result) {
            throw new FatalErrorException($this->__('The category could not be created.'));
        }

        $output = array(
            'id' => $result,
            'name' => $name,
            'url' => ModUtil::url('ZikulaAdminModule', 'admin', 'adminpanel', array('acid' => $result))
        );

        return new Zikula_Response_Ajax($output);
    }

    /**
     * Delete an admin category by ajax.
     *
     * @return \Zikula_Response_Ajax Ajax response containing the category id on success
     *
     * @throws \FatalErrorException Thrown if the supplied category doesn't exist or
     *                                     if the category couldn't be deleted
     */
    public function deleteCategoryAction()
    {
        $this->checkAjaxToken();

        //get passed cid to delete
        $cid = trim($this->request->request->get('cid'));

        //check user has permission to delete this
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::Category', "::$cid", ACCESS_DELETE));

        //find the category corresponding to the cid.
        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'get', array('cid' => $cid));
        if (empty($item)) {
            throw new FatalErrorException($this->__('Error! No such category found.'));
        }

        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::Category', "$item[name]::$item[cid]", ACCESS_DELETE));

        $output = array();

        //delete the category
        $delete = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'delete', array('cid' => $cid));
        if ($delete) {
            // Success
            $output['response'] = $cid;

            return new Zikula_Response_Ajax($output);
        }

        //unknown error
        throw new FatalErrorException($this->__('Error! Could not perform the deletion.'));
    }

    /**
     * Edit an admin category by ajax.
     *
     * @return \Zikula_Response_Ajax Ajax response containing the name of the edited category
     *
     * @throws \InvalidArgumentException Thrown if either the category id or name are not supplied or null
     * @throws \FatalErrorException Thrown if the new category name already exists or
     *                                     if the category id couldn't be found or
     *                                     if the changes to the category couldn't be saved
     */
    public function editCategoryAction()
    {
        $this->checkAjaxToken();

        //get form values
        $cid = trim($this->request->request->get('cid'));
        $name = trim($this->request->request->get('name'));

        //security checks
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::Category', "$name::$cid", ACCESS_EDIT));

        //make sure cid and category name (cat) are both set
        if (!isset($cid) || $cid == '' || !isset($name) || $name == '') {
            throw new \InvalidArgumentException($this->__('No category name or id set.'));
        }

        $output = array();

        //check if category with same name exists
        $cats = array();
        $items = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        foreach ($items as $item) {
            if (SecurityUtil::checkPermission('ZikulaAdminModule::', "$item[name]::$item[cid]", ACCESS_READ)) {
                $cats[] = $item;
            }
        }

        foreach ($cats as $cat) {
           if ($name == $cat['name']) {
                //check to see if the category with same name is the same category.
                if ($cat['cid'] == $cid) {
                    $output['response'] = $name;
                    return new Zikula_Response_Ajax($output);
                }

                //a different category has the same name, not allowed.
                throw new FatalErrorException($this->__('Error! A category by this name already exists.'));
            }
        }

        //get the category from the database
        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'get', array('cid' => $cid));
        if (empty($item)) {
            throw new FatalErrorException($this->__('Error! No such category found.'));
        }

        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::Category', "$item[name]::$item[cid]", ACCESS_EDIT));

        // update the category using the info from the database and from the form.
        $update = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'update', array('cid' => $cid, 'name' => $name, 'description' => $item['description']));
        if ($update) {
            $output['response'] = $name;
            return new Zikula_Response_Ajax($output);
        }

        //update failed for some reason
        throw new FatalErrorException($this->__('Error! Could not save your changes.'));
    }

    /**
     * Make a category the initially selected one (by ajax).
     *
     * @return \Zikula_Response_Ajax Ajax response containg a sucess message
     *
     * @throws \FatalErrorException Thrown if the category couldn't be found or 
     *                                     if the category couldn't be set as the default
     */
    public function defaultCategoryAction()
    {
        $this->checkAjaxToken();

        //check user has permission to change the initially selected category
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN));

        //get passed cid
        $cid = trim($this->request->request->get('cid'));

        //find the category corresponding to the cid.
        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'get', array('cid' => $cid));
        if ($item == false) {
            throw new FatalErrorException($this->__('Error! No such category found.'));
        }

        $output = array();

        // make category the initially selected one
        $makedefault = ModUtil::setVar('ZikulaAdminModule', 'startcategory', $cid);
        if ($makedefault) {
            // Success
            $output['response'] = $this->__f('Category "%s" was successfully made default.', $item['name']);
            return new Zikula_Response_Ajax($output);
        }

        //unknown error
        throw new FatalErrorException($this->__('Error! Could not make this category default.'));
    }

    /**
     * Sort the admin categories 
     *
     * @return \Zikula_Response_Ajax Ajax response containing a null array on success.
     */
    public function sortCategoriesAction()
    {
        $this->checkAjaxToken();

        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN));

        $data = $this->request->request->get('admintabs');

        $entity = 'Zikula\Module\AdminModule\Entity\AdminCategoryEntity';

        foreach ($data as $order => $cid) {
            $item = $this->entityManager->getRepository($entity)->findOneBy(array('cid' => $cid));
            $item->setSortorder($order);
        }

        $this->entityManager->flush();

        return new Zikula_Response_Ajax(array());
    }

    /**
     * Sort the modules
     *
     * @return \Zikula_Response_Ajax Ajax response containing a null array on success.
     */
    public function sortModulesAction()
    {
        $this->checkAjaxToken();

        $this->throwForbiddenUnless(SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN));

        $data = $this->request->request->get('modules');

        $entity = 'Zikula\Module\AdminModule\Entity\AdminModuleEntity';

        foreach ($data as $order => $mid) {
            $item = $this->entityManager->getRepository($entity)->findOneBy(array('mid' => $mid));
            $item->setSortorder($order);
        }

        $this->entityManager->flush();

        return new Zikula_Response_Ajax(array());
    }
}

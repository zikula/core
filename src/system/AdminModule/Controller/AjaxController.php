<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\AdminModule\Controller;

use SecurityUtil;
use ModUtil;
use DataUtil;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\NotFoundResponse;
use Zikula\Core\Response\Ajax\FatalResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
use Zikula\Core\Response\Ajax\BadDataResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove

/**
 * @Route("/ajax")
 *
 * Ajax controllers for the admin module
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * @Route("/assigncategory", options={"expose"=true})
     *
     * Change the category a module belongs to by ajax.
     *
     * @return ForbiddenResponse on perm check failure
     * @return NotFoundResponse if module name cannot be found
     * @return FatalResponse if cannot add module to category
     * @return AjaxResponse Ajax response containing the moduleid on success.
     */
    public function changeModuleCategoryAction()
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        $moduleID = $this->request->request->get('modid');
        $newParentCat = (int)$this->request->request->get('cat');

        //get info on the module
        $module = ModUtil::getInfo($moduleID);
        if (!$module) {
            //deal with couldn't get module info
            return new NotFoundResponse($this->__('Error! Could not get module name for id %s.'));
        }

        //get the module name
        $displayname = DataUtil::formatForDisplay($module['displayname']);
        $url = isset($module['capabilities']['admin']['url'])
            ? $module['capabilities']['admin']['url']
            : $this->get('router')->generate($module['capabilities']['admin']['route']);
        $module = $module['name'];
        $oldcid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory', array('mid' => $moduleID));

        //move the module
        $result = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', array('category' => $newParentCat, 'module' => $module));
        if (!$result) {
            return new FatalResponse($this->__('Error! Could not add module to module category.'));
        }

        $output = array(
            'id' => $moduleID,
            'name' => $displayname,
            'url' => $url,
            'parentCategory' => $newParentCat,
            'oldCategory' => $oldcid,
        );

        return new AjaxResponse($output);
    }

    /**
     * @Route("/newcategory", options={"expose"=true})
     *
     * Add a new admin category by ajax.
     *
     * @return ForbiddenResponse on perm check failure
     * @return FatalResponse if category cannot be created
     * @return BadDataResponse if category name already exists
     * @return AjaxResponse Ajax response containing the new cid on success
     */
    public function addCategoryAction()
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

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
                return new BadDataResponse($this->__('Error! A category by this name already exists.'));
            }
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaAdminModule::Category', "$name::", ACCESS_ADD)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //create the category
        $result = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'create', array('name' => $name, 'description' => ''));
        if (!$result) {
            return new FatalResponse($this->__('The category could not be created.'));
        }

        $output = array(
            'id' => $result,
            'name' => $name,
            'url' => $this->get('router')->generate('zikulaadminmodule_admin_adminpanel', array('acid' => $result)),
        );

        return new AjaxResponse($output);
    }

    /**
     * @Route("/deletecategory", options={"expose"=true})
     *
     * Delete an admin category by ajax.
     *
     * @return ForbiddenResponse on perm check failure
     * @return NotFoundResponse if category not found
     * @return FatalResponse if cannot delete
     * @return AjaxResponse Ajax response containing the category id on success
     */
    public function deleteCategoryAction()
    {
        $this->checkAjaxToken();

        //get passed cid to delete
        $cid = trim($this->request->request->get('cid'));

        //check user has permission to delete this
        if (!SecurityUtil::checkPermission('ZikulaAdminModule::Category', "::$cid", ACCESS_DELETE)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //find the category corresponding to the cid.
        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', array('cid' => $cid));
        if (empty($item)) {
            return new NotFoundResponse($this->__('Error! No such category found.'));
        }

        if (!SecurityUtil::checkPermission('ZikulaAdminModule::Category', "$item[name]::$item[cid]", ACCESS_DELETE)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        $output = array();

        //delete the category
        $delete = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'delete', array('cid' => $cid));
        if ($delete) {
            // Success
            $output['response'] = $cid;

            return new AjaxResponse($output);
        }

        //unknown error
        return new FatalResponse($this->__('Error! Could not perform the deletion.'));
    }

    /**
     * @Route("/editcategory", options={"expose"=true})
     *
     * Edit an admin category by ajax.
     *
     * @return ForbiddenResponse on perm check failure
     * @return NotFoundResponse if category not found
     * @return FatalResponse if cannot save changes
     * @return BadDataResponse if category name|id not set or category already exists
     * @return AjaxResponse Ajax response containing the name of the edited category
     */
    public function editCategoryAction()
    {
        $this->checkAjaxToken();

        //get form values
        $cid = trim($this->request->request->get('cid'));
        $name = trim($this->request->request->get('name'));

        //security checks
        if (!SecurityUtil::checkPermission('ZikulaAdminModule::Category', "$name::$cid", ACCESS_EDIT)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //make sure cid and category name (cat) are both set
        if (!isset($cid) || $cid == '' || !isset($name) || $name == '') {
            return new BadDataResponse($this->__('No category name or id set.'));
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

                    return new AjaxResponse($output);
                }

                //a different category has the same name, not allowed.
                return new BadDataResponse($this->__('Error! A category by this name already exists.'));
            }
        }

        //get the category from the database
        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', array('cid' => $cid));
        if (empty($item)) {
            return new NotFoundResponse($this->__('Error! No such category found.'));
        }

        if (!SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        // update the category using the info from the database and from the form.
        $update = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'update', array('cid' => $cid, 'name' => $name, 'description' => $item['description']));
        if ($update) {
            $output['response'] = $name;

            return new AjaxResponse($output);
        }

        //update failed for some reason
        return new FatalResponse($this->__('Error! Could not save your changes.'));
    }

    /**
     * @Route("/makedefault", options={"expose"=true})
     *
     * Make a category the initially selected one (by ajax).
     *
     * @return ForbiddenResponse on perm check failure
     * @return NotFoundResponse if category not found
     * @return FatalResponse if cannot make the category the default
     * @return AjaxResponse Ajax response containing a success message
     */
    public function defaultCategoryAction()
    {
        $this->checkAjaxToken();

        //check user has permission to change the initially selected category
        if (!SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //get passed cid
        $cid = trim($this->request->request->get('cid'));

        //find the category corresponding to the cid.
        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', array('cid' => $cid));
        if ($item == false) {
            return new NotFoundResponse($this->__('Error! No such category found.'));
        }

        $output = array();

        // make category the initially selected one
        $makedefault = ModUtil::setVar('ZikulaAdminModule', 'startcategory', $cid);
        if ($makedefault) {
            // Success
            $output['response'] = $this->__f('Category "%s" was successfully made default.', $item['name']);

            return new AjaxResponse($output);
        }

        //unknown error
        return new FatalResponse($this->__('Error! Could not make this category default.'));
    }

    /**
     * @Route("/sortcategories", options={"expose"=true})
     *
     * Sort the admin categories 
     *
     * @return ForbiddenResponse on perm check failure
     * @return AjaxResponse Ajax response containing a null array on success.
     */
    public function sortCategoriesAction()
    {
        $this->checkAjaxToken();

        if (!SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        $data = $this->request->request->get('admintabs');

        $entity = 'ZikulaAdminModule:AdminCategoryEntity';

        foreach ($data as $order => $cid) {
            $item = $this->entityManager->getRepository($entity)->findOneBy(array('cid' => $cid));
            $item->setSortorder($order);
        }

        $this->entityManager->flush();

        return new AjaxResponse(array());
    }

    /**
     * @Route("/sortmodules", options={"expose"=true})
     *
     * Sort the modules
     *
     * @return ForbiddenResponse on perm check failure
     * @return AjaxResponse Ajax response containing a null array on success.
     */
    public function sortModulesAction()
    {
        $this->checkAjaxToken();

        if (!SecurityUtil::checkPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        $data = $this->request->request->get('modules');

        $entity = 'ZikulaAdminModule:AdminModuleEntity';

        foreach ($data as $order => $mid) {
            $item = $this->entityManager->getRepository($entity)->findOneBy(array('mid' => $mid));
            $item->setSortorder($order);
        }

        $this->entityManager->flush();

        return new AjaxResponse(array());
    }
}

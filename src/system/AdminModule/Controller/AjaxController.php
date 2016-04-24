<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Controller;

use DataUtil;
use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\BadDataResponse;
use Zikula\Core\Response\Ajax\FatalResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
use Zikula\Core\Response\Ajax\NotFoundResponse;

/**
 * @Route("/ajax")
 *
 * Ajax controllers for the admin module
 */
class AjaxController extends AbstractController
{
    /**
     * @Route("/assigncategory", options={"expose"=true})
     *
     * Change the category a module belongs to by ajax.
     *
     * @param Request $request
     *
     * @return ForbiddenResponse on perm check failure
     * @return NotFoundResponse if module name cannot be found
     * @return FatalResponse if cannot add module to category
     * @return AjaxResponse Ajax response containing the moduleid on success.
     */
    public function changeModuleCategoryAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        $moduleId = $request->request->get('modid');
        $newParentCat = $request->request->getDigits('cat');

        //get info on the module
        $module = ModUtil::getInfo($moduleId);
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
        $oldcid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory', ['mid' => $moduleId]);

        //move the module
        $result = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', ['category' => $newParentCat, 'module' => $module]);
        if (!$result) {
            return new FatalResponse($this->__('Error! Could not add module to module category.'));
        }

        $output = [
            'id' => $moduleId,
            'name' => $displayname,
            'url' => $url,
            'parentCategory' => $newParentCat,
            'oldCategory' => $oldcid,
        ];

        return new AjaxResponse($output);
    }

    /**
     * @Route("/newcategory", options={"expose"=true})
     *
     * Add a new admin category by ajax.
     *
     * @param Request $request
     *
     * @return ForbiddenResponse on perm check failure
     * @return FatalResponse if category cannot be created
     * @return BadDataResponse if category name already exists
     * @return AjaxResponse Ajax response containing the new cid on success
     */
    public function addCategoryAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //get form information
        $name = trim($request->request->get('name'));

        // make sure name is set.
        if ($name == '') {
            return new BadDataResponse($this->__('Error! No category name given.'));
        }

        //check if there exists a cat with this name.
        $categories = [];
        $items = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', "$item[name]::$item[cid]", ACCESS_READ)) {
                $categories[] = $item;
            }
        }

        foreach ($categories as $cat) {
            if ($name == $cat['name']) {
                return new BadDataResponse($this->__('Error! A category by this name already exists.'));
            }
        }

        // Security check
        if (!$this->hasPermission('ZikulaAdminModule::Category', "$name::", ACCESS_ADD)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //create the category
        $result = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'create', ['name' => $name, 'description' => '']);
        if (!$result) {
            return new FatalResponse($this->__('The category could not be created.'));
        }

        $output = [
            'id' => $result,
            'name' => $name,
            'url' => $this->get('router')->generate('zikulaadminmodule_admin_adminpanel', ['acid' => $result]),
        ];

        return new AjaxResponse($output);
    }

    /**
     * @Route("/deletecategory", options={"expose"=true})
     *
     * Delete an admin category by ajax.
     *
     * @param Request $request
     *
     * @return ForbiddenResponse on perm check failure
     * @return NotFoundResponse if category not found
     * @return FatalResponse if cannot delete
     * @return AjaxResponse Ajax response containing the category id on success
     */
    public function deleteCategoryAction(Request $request)
    {
        //get passed cid to delete
        $cid = trim($request->request->getDigits('cid'));

        //check user has permission to delete this
        if (!$this->hasPermission('ZikulaAdminModule::Category', "::$cid", ACCESS_DELETE)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //find the category corresponding to the cid.
        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', ['cid' => $cid]);
        if (empty($item)) {
            return new NotFoundResponse($this->__('Error! No such category found.'));
        }

        if (!$this->hasPermission('ZikulaAdminModule::Category', "$item[name]::$item[cid]", ACCESS_DELETE)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        $output = [];

        //delete the category
        $delete = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'delete', ['cid' => $cid]);
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
     * @param Request $request
     *
     * @return ForbiddenResponse on perm check failure
     * @return NotFoundResponse if category not found
     * @return FatalResponse if cannot save changes
     * @return BadDataResponse if category name|id not set or category already exists
     * @return AjaxResponse Ajax response containing the name of the edited category
     */
    public function editCategoryAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //get form values
        $cid = trim($request->request->getDigits('cid'));
        $name = trim($request->request->get('name'));

        //security checks
        if (!$this->hasPermission('ZikulaAdminModule::Category', $name . '::' . $cid, ACCESS_EDIT)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //make sure cid and category name (cat) are both set
        if (!isset($cid) || $cid == '' || !isset($name) || $name == '') {
            return new BadDataResponse($this->__('No category name or id set.'));
        }

        $output = [];

        //check if category with same name exists
        $categories = [];
        $items = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                $categories[] = $item;
            }
        }

        foreach ($categories as $cat) {
            if ($name != $cat['name']) {
                continue;
            }

            //check to see if the category with same name is the same category.
            if ($cat['cid'] == $cid) {
                $output['response'] = $name;

                return new AjaxResponse($output);
            }

            //a different category has the same name, not allowed.
            return new BadDataResponse($this->__('Error! A category by this name already exists.'));
        }

        //get the category from the database
        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', ['cid' => $cid]);
        if (empty($item)) {
            return new NotFoundResponse($this->__('Error! No such category found.'));
        }

        // update the category using the info from the database and from the form.
        $update = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'update', [
            'cid' => $cid,
            'name' => $name,
            'description' => $item['description']
        ]);
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
     * @param Request $request
     *
     * @return ForbiddenResponse on perm check failure
     * @return NotFoundResponse if category not found
     * @return FatalResponse if cannot make the category the default
     * @return AjaxResponse Ajax response containing a success message
     */
    public function defaultCategoryAction(Request $request)
    {
        //check user has permission to change the initially selected category
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        //get passed cid
        $cid = trim($request->request->getDigits('cid'));

        //find the category corresponding to the cid.
        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', ['cid' => $cid]);
        if (false == $item) {
            return new NotFoundResponse($this->__('Error! No such category found.'));
        }

        $output = [];

        // make category the initially selected one
        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $makeDefault = $variableApi->set('ZikulaAdminModule', 'startcategory', $cid);
        if ($makeDefault) {
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
     * @param Request $request
     *
     * @return ForbiddenResponse on perm check failure
     * @return AjaxResponse Ajax response containing a null array on success.
     */
    public function sortCategoriesAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        $data = $request->request->get('admintabs');

        $entity = 'ZikulaAdminModule:AdminCategoryEntity';

        foreach ($data as $order => $cid) {
            $item = $this->entityManager->getRepository($entity)->findOneBy(['cid' => $cid]);
            $item->setSortorder($order);
        }

        $this->entityManager->flush();

        return new AjaxResponse([]);
    }

    /**
     * @Route("/sortmodules", options={"expose"=true})
     *
     * Sort the modules
     *
     * @param Request $request
     *
     * @return ForbiddenResponse on perm check failure
     * @return AjaxResponse Ajax response containing a null array on success.
     */
    public function sortModulesAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('Access forbidden.'));
        }

        $data = $request->request->get('modules');

        $entity = 'ZikulaAdminModule:AdminModuleEntity';

        foreach ($data as $order => $mid) {
            $item = $this->entityManager->getRepository($entity)->findOneBy(['mid' => $mid]);
            $item->setSortorder($order);
        }

        $this->entityManager->flush();

        return new AjaxResponse([]);
    }
}

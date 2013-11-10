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
 * @subpackage ZikulaCategoriesModule
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\CategoriesModule\Controller;

use LogUtil;
use SecurityUtil;
use ModUtil;
use System;
use FormUtil;
use CategoryUtil;
use Zikula\Module\CategoriesModule\GenericUtil;
use Zikula\Module\CategoriesModule\Entity\CategoryEntity;
use Zikula\Module\CategoriesModule\Entity\CategoryRegistryEntity;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Form controller for the categories module
 *
 */
class AdminformController extends \Zikula_AbstractController
{
    /**
     * update category
     *
     * @return void
     *
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have admin permissions over the module
     */
    public function editAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        // get data from post
        $data = $this->request->request->get('category', null);

        if (!isset($data['is_locked'])) {
            $data['is_locked'] = 0;
        }
        if (!isset($data['is_leaf'])) {
            $data['is_leaf'] = 0;
        }
        if (!isset($data['status'])) {
            $data['status'] = 'I';
        }

        $args = array();

        if ($this->request->request->get('category_copy', null)) {
            $args['op'] = 'copy';
            $args['cid'] = (int)$data['id'];
            return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'op', $args));
        }

        if ($this->request->request->get('category_move', null)) {
            $args['op'] = 'move';
            $args['cid'] = (int)$data['id'];
            return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'op', $args));
        }

        if ($this->request->request->get('category_delete', null)) {
            $args['op'] = 'delete';
            $args['cid'] = (int)$data['id'];
            return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'op', $args));
        }

        if ($this->request->request->get('category_user_edit', null)) {
            $_SESSION['category_referer'] = System::serverGetVar('HTTP_REFERER');
            $args['dr'] = (int)$data['id'];
            return $this->redirect(ModUtil::url('Categories', 'user', 'edit', $args));
        }

        $valid = GenericUtil::validateCategoryData($data);
        if (!$valid) {
            return $this->redirect(ModUtil::url('Categories', 'admin', 'edit', array('mode' => 'edit', 'cid' => (int)$data['id'])));
        }

        // process name
        $data['name'] = GenericUtil::processCategoryName($data['name']);

        // process parent
        $data['parent'] = GenericUtil::processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = GenericUtil::processCategoryDisplayName($data['display_name'], $data['name']);

        // get existing category
        $category = $this->entityManager->find('Zikula\Module\CategoriesModule\Entity\CategoryEntity', $data['id']);

        $prevCategoryName = $category['name'];

        // save category
        $category->merge($data);
        $this->entityManager->flush();

        // process path and ipath
        $category['path'] = GenericUtil::processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = GenericUtil::processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $this->request->request->get('attribute_name', array());
        $attrib_values = $this->request->request->get('attribute_value', array());
        GenericUtil::processCategoryAttributes($category, $attrib_names, $attrib_values);

        $this->entityManager->flush();

        // since a name change will change the object path, we must rebuild it here
        if ($prevCategoryName != $category['name']) {
            CategoryUtil::rebuildPaths('path', 'name', $category['id']);
        }

        $msg = __f('Done! Saved the %s category.', $prevCategoryName);
        LogUtil::registerStatus($msg);
        return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view'));
    }

    /**
     * create category
     *
     * @return void
     *
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to add a category
     */
    public function newcatAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedHttpException();
        }

        // get data from post
        $data = $this->request->request->get('category', null);

        $valid = GenericUtil::validateCategoryData($data);
        if (!$valid) {
            return $this->redirect(ModUtil::url('Categories', 'admin', 'newcat'));
        }

        // process name
        $data['name'] = GenericUtil::processCategoryName($data['name']);

        // process parent
        $data['parent'] = GenericUtil::processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = GenericUtil::processCategoryDisplayName($data['display_name'], $data['name']);

        // save category
        $category = new CategoryEntity();
        $category->merge($data);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // process path and ipath
        $category['path'] = GenericUtil::processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = GenericUtil::processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $this->request->request->get('attribute_name', array());
        $attrib_values = $this->request->request->get('attribute_value', array());
        GenericUtil::processCategoryAttributes($category, $attrib_names, $attrib_values);

        $this->entityManager->flush();

        $msg = __f('Done! Inserted the %s category.', $category['name']);
        LogUtil::registerStatus($msg);
        $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view') . '#top');
    }

    /**
     * delete category
     *
     * @return void
     *
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to delete a category
     */
    public function deleteAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedHttpException();
        }

        if ($this->request->request->get('category_cancel', null)) {
            return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view'));
        }

        $cid = $this->request->request->get('cid', null);

        $cat = CategoryUtil::getCategoryByID($cid);

        // delete subdirectories
        if ($this->request->request->get('subcat_action') == 'delete') {
            CategoryUtil::deleteCategoriesByPath($cat['ipath']);
        } elseif ($this->request->request->get('subcat_action') == 'move') {
            // move subdirectories
            $data = $this->request->request->get('category', null);
            if ($data['parent_id']) {
                CategoryUtil::moveSubCategoriesByPath($cat['ipath'], $data['parent_id']);
                CategoryUtil::deleteCategoryByID($cid);
            }
        }

        $msg = __f('Done! Deleted the %s category.', $cat['name']);
        LogUtil::registerStatus($msg);

        return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view'));
    }

    /**
     * copy category
     *
     * @return void
     *
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to add a category
     */
    public function copyAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedHttpException();
        }

        if ($this->request->request->get('category_cancel', null)) {
            return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view'));
        }

        $cid = $this->request->request->get('cid', null);
        $cat = CategoryUtil::getCategoryByID($cid);

        $data = $this->request->request->get('category', null);

        CategoryUtil::copyCategoriesByPath($cat['ipath'], $data['parent_id']);

        $msg = __f('Done! Copied the %s category.', $cat['name']);
        LogUtil::registerStatus($msg);

        return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view'));
    }

    /**
     * move category
     *
     * @return void
     *
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have permission to edit a category
     */
    public function moveAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedHttpException();
        }

        if ($this->request->request->get('category_cancel', null)) {
            return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view'));
        }

        $cid = $this->request->request->get('cid', null);
        $cat = CategoryUtil::getCategoryByID($cid);

        $data = $this->request->request->get('category', null);

        CategoryUtil::moveCategoriesByPath($cat['ipath'], $data['parent_id']);

        $msg = __f('Done! Moved the %s category.', $cat['name']);
        LogUtil::registerStatus($msg);

        return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view'));
    }

    /**
     * rebuild path structure
     *
     * @return void
     *
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have admin permissions over the module
     */
    public function rebuild_pathsAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        CategoryUtil::rebuildPaths('path', 'name');
        CategoryUtil::rebuildPaths('ipath', 'id');

        LogUtil::registerStatus(__('Done! Rebuilt the category paths.'));

        return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view'));
    }

    /**
     * edit category registry
     *
     * @return void
     *
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have admin permissions over the module
     * @throws \InvalidArgumentException Thrown if the module name isn't supplied or if
     *                                          if the entityname isn't supplied or if
     *                                          if the property isn't supplied or if
     *                                          if the category_id isn't supplied
     */
    public function editregistryAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        // delete registry
        if ($this->request->request->get('mode', null) == 'delete') {
            $id = $this->request->get('id', 0);
            $obj = $this->entityManager->find('Zikula\Module\CategoriesModule\Entity\CategoryRegistryEntity', $id);
            $this->entityManager->remove($obj);
            $this->entityManager->flush();

            LogUtil::registerStatus(__('Done! Deleted the category registry entry.'));

            return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'editregistry'));
        }

        $args = array();

        if (!$this->request->request->get('category_submit', null)) {
            // got here through selector auto-submit
            $data = $this->request->request->get('category_registry', null);
            $args['category_registry'] = $data;
            return $this->redirect(ModUtil::url('Categories', 'admin', 'editregistry', $args));
        }

        // get data from post
        $data = $this->request->request->get('category_registry', null);

        // do some validation
        if (empty($data['modname'])) {
            throw new \InvalidArgumentException(__('Error! You did not select a module.'));
        }
        if (empty($data['entityname'])) {
            throw new \InvalidArgumentException(__('Error! You did not select an entity.'));
        }
        if (empty($data['property'])) {
            throw new \InvalidArgumentException(__('Error! You did not enter a property name.'));
        }
        if ((int)$data['category_id'] == 0) {
            throw new \InvalidArgumentException(__('Error! You did not select a category.'));
        }

        if (isset($data['id']) && (int)$data['id'] > 0) {
            // update existing registry
            $obj = $this->entityManager->find('Zikula\Module\CategoriesModule\Entity\CategoryRegistryEntity', $data['id']);
        } else {
            // create new registry
            $obj = new CategoryRegistryEntity();
        }
        $obj->merge($data);
        $this->entityManager->persist($obj);
        $this->entityManager->flush();
        LogUtil::registerStatus(__('Done! Saved the category registry entry.'));

        return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'editregistry'));
    }

    /**
     * edit module perferences
     *
     * @return void
     *
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Thrown if the user doesn't have admin permissions over the module
     */
    public function preferencesAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedHttpException();
        }

        $userrootcat = $this->request->get('userrootcat', null);
        if ($userrootcat) {
            $this->setVar('userrootcat', $userrootcat);
        }

        $autocreateusercat = (int)$this->request->get('autocreateusercat', 0);
        $this->setVar('autocreateusercat', $autocreateusercat);

        $allowusercatedit = (int)$this->request->get('allowusercatedit', 0);
        $this->setVar('allowusercatedit', $allowusercatedit);

        $autocreateuserdefaultcat = $this->request->get('autocreateuserdefaultcat', 0);
        $this->setVar('autocreateuserdefaultcat', $autocreateuserdefaultcat);

        $userdefaultcatname = $this->request->get('userdefaultcatname', 'Default');
        $this->setVar('userdefaultcatname', $userdefaultcatname);

        $permissionsall = (int)$this->request->get('permissionsall', 0);
        $this->setVar('permissionsall', $permissionsall);

        LogUtil::registerStatus(__('Done! Saved module configuration.'));

        return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'preferences'));
    }
}
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

namespace Zikula\Module\CategoriesModule\Controller;

use Zikula_View;
use ModUtil;
use FormUtil;
use SecurityUtil;
use CategoryUtil;
use ZLanguage;
use StringUtil;
use System;
use Zikula\Module\CategoriesModule\Entity\CategoryEntity;
use Zikula\Module\CategoriesModule\Entity\CategoryRegistryEntity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Administrative controllers for the categories module
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * main admin function
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        // Security check will be done in view()
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'view')));
    }

    /**
     * view categories
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to edit the category
     */
    public function viewAction()
    {
        $root_id = $this->request->get('dr', 1);

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', "ID::$root_id", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $cats = CategoryUtil::getSubCategories($root_id, true, true, true, true, true);
        $menuTxt = CategoryUtil::getCategoryTreeJS($cats, true, true);

        $this->view->assign('menuTxt', $menuTxt);

        return $this->response($this->view->fetch('Admin/view.tpl'));
    }

    /**
     * display configure module page
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module configuration
     */
    public function configAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return $this->response($this->view->fetch('Admin/config.tpl'));
    }

    /**
     * edit category
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to edit or add the category
     * @throws \RuntimeException Thrown if a valid category ID isn't supplied
     * @throws NotFoundHttpException Thrown if the category isn't found 
     */
    public function editAction()
    {
        $cid = $this->request->get('cid', 0);
        $root_id = $this->request->get('dr', 1);
        $mode = $this->request->get('mode', 'new');
        $allCats = '';
        $editCat = '';

        $languages = ZLanguage::getInstalledLanguages();

        // indicates that we're editing
        if ($mode == 'edit') {
            if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
                throw new AccessDeniedException();
            }

            if (!$cid) {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Error! Cannot determine valid \'cid\' for edit mode in \'ZikulaCategoriesModule_admin_edit\'.'));
                return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'view')));
            }

            $editCat = CategoryUtil::getCategoryByID($cid);
            if (!$editCat) {
                $this->request->getSession()->getFlashbag()->add('error', $this->__('Sorry! No such item found.'));
                return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'view')));
            }
        } else {
            // new category creation
            if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_ADD)) {
                throw new AccessDeniedException();
            }

            // since we inherit the domain settings from the parent, we get
            // the inherited (and merged) object from session
            if (isset($_SESSION['newCategory']) && $_SESSION['newCategory']) {
                $editCat = $_SESSION['newCategory'];
                unset($_SESSION['newCategory']);
                $category = new CategoryEntity(); // need this for validation info
            } elseif (FormUtil::getValidationErrors()) {
                // if we're back from validation get the posted data from session
                $newCatActionData = \SessionUtil::getVar('newCatActionData');
                \SessionUtil::delVar('newCatActionData');
                $editCat = new CategoryEntity();
                $editCat = $editCat->toArray();
                $editCat = array_merge($editCat, $newCatActionData);
                unset($editCat['path']);
                unset($editCat['ipath']);
                $category = new CategoryEntity(); // need this for validation info
            } else {
                // someone just pressed 'new' -> populate defaults
                $category = new CategoryEntity();
                $editCat['sort_value'] = '0';
            }
        }

        $allCats = CategoryUtil::getSubCategories($root_id, true, true, true, false, true);

        // now remove the categories which are below $editCat ...
        // you should not be able to set these as a parent category as it creates a circular hierarchy (see bug #4992)
        if (isset($editCat['ipath'])) {
            $cSlashEdit = StringUtil::countInstances($editCat['ipath'], '/');
            foreach ($allCats as $k => $v) {
                $cSlashCat = StringUtil::countInstances($v['ipath'], '/');
                if ($cSlashCat >= $cSlashEdit && strpos($v['ipath'], $editCat['ipath']) !== false) {
                    unset($allCats[$k]);
                }
            }
        }

        $selector = CategoryUtil::getSelector_Categories($allCats, 
                                                         'id',
                                                         (isset($editCat['parent_id']) ? $editCat['parent_id'] : 0),
                                                         'category[parent_id]',
                                                         isset($defaultValue) ? $defaultValue : null,
                                                         null,
                                                         0,
                                                         null,
                                                         false, // do not submit on selector change
                                                         false,
                                                         true,
                                                         1,
                                                         false,
                                                         'form-control');

        $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : array();

        $this->view->assign('mode', $mode)
                   ->assign('category', $editCat)
                   ->assign('attributes', $attributes)
                   ->assign('languages', $languages)
                   ->assign('categorySelector', $selector);

        if ($mode == 'edit') {
            $this->view->assign('haveSubcategories', CategoryUtil::haveDirectSubcategories($cid))
                       ->assign('haveLeafSubcategories', CategoryUtil::haveDirectSubcategories($cid, false, true));
        }

        return $this->response($this->view->fetch('Admin/edit.tpl'));
    }

    /**
     * edit category registry
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function editregistryAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $root_id = $this->request->get('dr', 1);
        $id = $this->request->get('id', 0);

        $obj = new CategoryRegistryEntity();

        $category_registry = $this->request->query->get('category_registry', null);
        if ($category_registry) {
            $obj->merge($category_registry);
            $obj = $obj->toArray();
        }

        $registries = $this->entityManager->getRepository('Zikula\Module\CategoriesModule\Entity\CategoryRegistryEntity')->findBy(array(), array('modname' => 'ASC', 'property' => 'ASC'));

        $this->view->assign('objectArray', $registries)
                   ->assign('newobj', $obj)
                   ->assign('root_id', $root_id)
                   ->assign('id', $id);

        return $this->response($this->view->fetch('Admin/registry_edit.tpl'));
    }

    /**
     * delete category registry
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function deleteregistryAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $id = $this->request->get('id', 0);

        $obj = $this->entityManager->find('Zikula\Module\CategoriesModule\Entity\CategoryRegistryEntity', $id);
        $data = $obj->toArray();

        $this->view->assign('data', $data)
                   ->assign('id', $id);

        return $this->response($this->view->fetch('Admin/registry_delete.tpl'));
    }

    /**
     * display new category form
     *
     * @return Response symfony response object
     */
    public function newcatAction()
    {
        $_POST['mode'] = 'new';
        $this->request->query->set('mode', 'new');
        return $this->editAction();
    }

    /**
     * generic function to handle copy, delete and move operations
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have access to delete the category
     */
    public function opAction()
    {
        $cid = $this->request->get('cid', 1);
        $root_id = $this->request->get('dr', 1);
        $op = $this->request->get('op', 'NOOP');

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', "ID::$cid", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $category = CategoryUtil::getCategoryByID($cid);
        $subCats = CategoryUtil::getSubCategories($cid, false, false);
        $allCats = CategoryUtil::getSubCategories($root_id, true, true, true, false, true, $cid);
        $selector = CategoryUtil::getSelector_Categories($allCats);

        $this->view->assign('category', $category)
                   ->assign('numSubcats', count($subCats))
                   ->assign('categorySelector', $selector);

        return $this->response($this->view->fetch("Admin/{$op}.tpl"));
    }

    /**
     * global module preferences
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function preferencesAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::preferences', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->view->assign('userrootcat', $this->getVar('userrootcat', '/__SYSTEM__'))
                   ->assign('allowusercatedit', $this->getVar('allowusercatedit', 0))
                   ->assign('autocreateusercat', $this->getVar('autocreateusercat', 0))
                   ->assign('autocreateuserdefaultcat', $this->getVar('autocreateuserdefaultcat', 0))
                   ->assign('userdefaultcatname', $this->getVar('userdefaultcatname', 0))
                   ->assign('permissionsall', $this->getVar('permissionsall', 0));

        return $this->response($this->view->fetch('Admin/preferences.tpl'));
    }
}

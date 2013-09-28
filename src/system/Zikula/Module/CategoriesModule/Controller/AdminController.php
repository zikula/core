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

namespace Zikula\Module\CategoriesModule\Controller;

use Zikula_View;
use ModUtil;
use FormUtil;
use LogUtil;
use SecurityUtil;
use CategoryUtil;
use ZLanguage;
use StringUtil;
use Categories_DBObject_Category;
use DBObject;

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
     */
    public function mainAction()
    {
        // Security check will be done in view()
        return $this->redirect(ModUtil::url('ZikulaCategoriesModule', 'admin', 'view'));
    }

    /**
     * view categories
     */
    public function viewAction()
    {
        $root_id = $this->request->get('dr', 1);

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', "ID::$root_id", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $cats = CategoryUtil::getSubCategories($root_id, true, true, true, true, true);
        $menuTxt = CategoryUtil::getCategoryTreeJS($cats, true, true);

        $this->view->assign('menuTxt', $menuTxt);

        return $this->response($this->view->fetch('Admin/view.tpl'));
    }

    /**
     * display configure module page
     */
    public function configAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        return $this->response($this->view->fetch('Admin/config.tpl'));
    }

    /**
     * edit category
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
            if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', "::", ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }

            if (!$cid) {
                return LogUtil::registerError($this->__('Error! Cannot determine valid \'cid\' for edit mode in \'ZikulaCategoriesModule_admin_edit\'.'));
            }

            $category = new Categories_DBObject_Category();
            $editCat = $category->select($cid);
            if ($editCat == false) {
                return LogUtil::registerError($this->__('Sorry! No such item found.'), 404);
            }
        } else {
            // new category creation
            if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_ADD)) {
                return LogUtil::registerPermissionError();
            }

            // since we inherit the domain settings from the parent, we get
            // the inherited (and merged) object from session
            if (isset($_SESSION['newCategory']) && $_SESSION['newCategory']) {
                $editCat = $_SESSION['newCategory'];
                unset($_SESSION['newCategory']);
                $category = new Categories_DBObject_Category(); // need this for validation info
            }
            // if we're back from validation get the object from input
            elseif (FormUtil::getValidationErrors()) {
                $category = new Categories_DBObject_Category(DBObject::GET_FROM_VALIDATION_FAILED); // need this for validation info
                $editCat = $category->get();
            }
            // someone just pressen 'new' -> populate defaults
            else {
                $category = new Categories_DBObject_Category(); // need this for validation info
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
                ->assign('categorySelector', $selector)
                ->assign('validation', $category->_objValidation);

        if ($mode == 'edit') {
            $this->view->assign('haveSubcategories', CategoryUtil::haveDirectSubcategories($cid))
                    ->assign('haveLeafSubcategories', CategoryUtil::haveDirectSubcategories($cid, false, true));
        }

        return $this->response($this->view->fetch('Admin/edit.tpl'));
    }

    public function editregistryAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $root_id = $this->request->get('dr', 1);
        $id = $this->request->get('id', 0);
        $ot = FormUtil::getPassedValue('ot', 'registry');

        $class = "Categories_DBObject_" . ucwords($ot);
        $arrayClass = "Categories_DBObject_" . ucwords($ot) . 'Array';

        $obj = new $class ();
        $data = $obj->getDataFromInput();
        if (!$data) {
            $data = $obj->getFailedValidationData();
            if (!$data) {
                $data = array();
            }
        }

        $where = '';
        $sort = 'modname, property';
        $objArray = new $arrayClass ();
        $dataA = $objArray->get($where, $sort);

        $this->view->assign('objectArray', $dataA)
                   ->assign('newobj', $data)
                   ->assign('root_id', $root_id)
                   ->assign('id', $id)
                   ->assign('validation', $obj->_objValidation);

        return $this->response($this->view->fetch('Admin/registry_edit.tpl'));
    }

    public function deleteregistryAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $id = $this->request->get('id', 0);
        $ot = FormUtil::getPassedValue('ot', 'registry');

        $class = "Categories_DBObject_" . ucwords($ot);

        $obj = new $class ();
        $data = $obj->get($id);

        $this->view->assign('data', $data)
                   ->assign('id', $id);

        return $this->response($this->view->fetch('Admin/registry_delete.tpl'));
    }

    /**
     * display new category form
     */
    public function newcatAction()
    {
        $_POST['mode'] = 'new';

        return $this->editAction();
    }

    /**
     * generic function to handle copy, delete and move operations
     */
    public function opAction()
    {
        $cid = $this->request->get('cid', 1);
        $root_id = $this->request->get('dr', 1);
        $op = $this->request->get('op', 'NOOP');

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', "ID::$cid", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        $category = new Categories_DBObject_Category();
        $category = $category->select($cid);
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
     */
    public function preferencesAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::preferences', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
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

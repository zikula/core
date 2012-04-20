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

namespace Categories\Controller;

use SecurityUtil, ModUtil, LogUtil, CategoryUtil, UserUtil, ZLanguage, FormUtil, DBObject;
use StringUtil;
use Categories\DBObject\Category;

/**
 * Controller.
 */
class AdminformController extends \Zikula_AbstractController
{
    /**
     * update category
     */
    public function editAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        $args = array();

        if ($this->request->request->get('category_copy', null)) {
            $args['op'] = 'copy';
            $args['cid'] = $_POST['category']['id'];
            return $this->redirect(ModUtil::url('Categories', 'admin', 'op', $args));
        }

        if ($this->request->request->get('category_move', null)) {
            $args['op'] = 'move';
            $args['cid'] = $_POST['category']['id'];
            return $this->redirect(ModUtil::url('Categories', 'admin', 'op', $args));
        }

        if ($this->request->request->get('category_delete', null)) {
            $args['op'] = 'delete';
            $args['cid'] = $_POST['category']['id'];
            return $this->redirect(ModUtil::url('Categories', 'admin', 'op', $args));
        }

        if ($this->request->request->get('category_user_edit', null)) {
            $_SESSION['category_referer'] = System::serverGetVar('HTTP_REFERER');
            $args['dr'] = $_POST['category']['id'];
            return $this->redirect(ModUtil::url('Categories', 'user', 'edit', $args));
        }

        $cat = new Category ();
        $data = $cat->getDataFromInput();

        if (!$cat->validate('admin')) {
            $category = $this->request->request->get('category', null);
            $args['cid'] = $category['id'];
            $args['mode'] = 'edit';
            return $this->redirect(ModUtil::url('Categories', 'admin', 'edit', $args));
        }

        $attributes = array();
        $values = $this->request->request->get('attribute_value');
        foreach ($this->request->request->get('attribute_name') as $index => $name) {
            if (!empty($name)) $attributes[$name] = $values[$index];
        }

        $cat->setDataField('__ATTRIBUTES__', $attributes);

        // retrieve old category from DB
        $category = $this->request->request->get('category', null);
        $oldCat = new Category(DBObject::GET_FROM_DB, $category['id']);

        // update new category data
        $cat->update();

        // since a name change will change the object path, we must rebuild it here
        if ($oldCat->_objData['name'] != $cat->_objData['name']) {
            $obj = $cat->_objData;
            CategoryUtil::rebuildPaths('path', 'name', $obj['id']);
        }

        $msg = __f('Done! Saved the %s category.', $oldCat->_objData['name']);
        LogUtil::registerStatus($msg);
        return $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    /**
     * create category
     */
    public function newcatAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        $cat = new Category ();
        $cat->getDataFromInput();

        // submit button wasn't pressed -> category was chosen from dropdown
        // we now get the parent (security) category domains so we can inherit them
        if (!$this->request->request->get('category_submit', null)) {
            $newCat = $_POST['category'];
            $pcID = $newCat['parent_id'];

            $pCat = new Category ();
            $parentCat = $pCat->get($pcID);

            //$newCat['security_domain'] = $parentCat['security_domain'];
            //for ($i=1; $i<=5; $i++) {
            //    $name = 'data' . $i . '_domain';
            //    $newCat[$name] = $parentCat[$name];
            //}

            $_SESSION['newCategory'] = $newCat;

            return $this->redirect(ModUtil::url('Categories', 'admin', 'newcat') . '#top');
        }

        if (!$cat->validate('admin')) {
            return $this->redirect(ModUtil::url('Categories', 'admin', 'newcat') . '#top');
        }

        $attributes = array();
        $values = $this->request->request->get('attribute_value', array());
        foreach ($this->request->request->get('attribute_name', array()) as $index => $name) {
            if (!empty($name)) {
                $attributes[$name] = $values[$index];
            }
        }

        if ($attributes) {
            $cat->setDataField('__ATTRIBUTES__', $attributes);
        }

        $cat->insert();
        // since the original insert can't construct the ipath (since
        // the insert id is not known yet) we update the object here.
        $cat->update();

        $msg = __f('Done! Inserted the %s category.', $cat->_objData['name']);
        LogUtil::registerStatus($msg);
        return $this->redirect(ModUtil::url('Categories', 'admin', 'view') . '#top');
    }

    /**
     * delete category
     */
    public function deleteAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_DELETE)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        if ($this->request->request->get('category_cancel', null)) {
            return $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
        }

        $cid = $this->request->request->get('cid', null);
        $cat = new Category ();
        $cat->get($cid);

        // delete subdirectories
        if ($_POST['subcat_action'] == 'delete') {
            $cat->delete(true);
        } elseif ($_POST['subcat_action'] == 'move') {
            // move subdirectories
            $cat->deleteMoveSubcategories($_POST['category']['parent_id']);
        }

        $msg = __f('Done! Deleted the %s category.', $cat->_objData['name']);
        LogUtil::registerStatus($msg);
        return $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    /**
     * copy category
     */
    public function copyAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        if ($this->request->request->get('category_cancel', null)) {
            return $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
        }

        $cid = $this->request->request->get('cid', null);
        $cat = new Category ();
        $cat->get($cid);

        $cat->copy($_POST['category']['parent_id']);

        $msg = __f('Done! Copied the %s category.', $cat->_objData['name']);
        LogUtil::registerStatus($msg);
        return $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    /**
     * move category
     */
    public function moveAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        if ($this->request->request->get('category_cancel', null)) {
            return $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
        }

        $cid = $this->request->request->get('cid', null);
        $cat = new Category ();
        $cat->get($cid);
        $cat->move($_POST['category']['parent_id']);

        $msg = __f('Done! Moved the %s category.', $cat->_objData['name']);
        LogUtil::registerStatus($msg);
        return $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    /**
     * rebuild path structure
     */
    public function rebuild_pathsAction()
    {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        CategoryUtil::rebuildPaths('path', 'name');
        CategoryUtil::rebuildPaths('ipath', 'id');

        LogUtil::registerStatus(__('Done! Rebuilt the category paths.'));
        return $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    public function editregistryAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        $id = $this->request->get('id', 0);

        $class = 'Categories_DBObject_Registry';

        if ($this->request->request->get('mode', null) == 'delete') {
            $obj = new $class();
            $obj->get($id);
            $obj->delete($id);

            LogUtil::registerStatus(__('Done! Deleted the category registry entry.'));
            return $this->redirect(ModUtil::url('Categories', 'admin', 'editregistry'));
        }

        $args = array();
        if (!$this->request->request->get('category_submit', null)) { // got here through selector auto-submit
            $obj = new $class();
            $data = $obj->getDataFromInput($id);
            $args['category_registry'] = $data;
            return $this->redirect(ModUtil::url('Categories', 'admin', 'editregistry', $args));
        }

        $obj = new $class();
        $obj->getDataFromInput();

        if (!$obj->validate('admin')) {
            return $this->redirect(ModUtil::url('Categories', 'admin', 'editregistry'));
        }

        $obj->save();
        LogUtil::registerStatus(__('Done! Saved the category registry entry.'));
        return $this->redirect(ModUtil::url('Categories', 'admin', 'editregistry'));
    }

    public function preferencesAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
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
        return $this->redirect(ModUtil::url('Categories', 'admin', 'preferences'));
    }

}
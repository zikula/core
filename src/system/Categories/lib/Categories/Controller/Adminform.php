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

/**
 * Controller.
 */
class Categories_Controller_Adminform extends Zikula_AbstractController
{
    /**
     * update category
     */
    public function edit()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $args = array();

        if (FormUtil::getPassedValue('category_copy', null, 'POST')) {
            $args['op'] = 'copy';
            $args['cid'] = $_POST['category']['id'];

            return System::redirect(ModUtil::url('Categories', 'admin', 'op', $args));
        }

        if (FormUtil::getPassedValue('category_move', null, 'POST')) {
            $args['op'] = 'move';
            $args['cid'] = $_POST['category']['id'];

            return System::redirect(ModUtil::url('Categories', 'admin', 'op', $args));
        }

        if (FormUtil::getPassedValue('category_delete', null, 'POST')) {
            $args['op'] = 'delete';
            $args['cid'] = $_POST['category']['id'];

            return System::redirect(ModUtil::url('Categories', 'admin', 'op', $args));
        }

        if (FormUtil::getPassedValue('category_user_edit', null, 'POST')) {
            $_SESSION['category_referer'] = System::serverGetVar('HTTP_REFERER');
            $args['dr'] = $_POST['category']['id'];

            return System::redirect(ModUtil::url('Categories', 'user', 'edit', $args));
        }

        $cat = new Categories_DBObject_Category ();
        $data = $cat->getDataFromInput();

        if (!$cat->validate('admin')) {
            $category = FormUtil::getPassedValue('category', null, 'POST');
            $args['cid'] = $category['id'];
            $args['mode'] = 'edit';

            return System::redirect(ModUtil::url('Categories', 'admin', 'edit', $args));
        }

        $attributes = array();
        $values = FormUtil::getPassedValue('attribute_value', 'POST');
        foreach (FormUtil::getPassedValue('attribute_name', 'POST') as $index => $name) {
            if (!empty($name)) $attributes[$name] = $values[$index];
        }

        $cat->setDataField('__ATTRIBUTES__', $attributes);

        // retrieve old category from DB
        $category = FormUtil::getPassedValue('category', null, 'POST');
        $oldCat = new Categories_DBObject_Category(DBObject::GET_FROM_DB, $category['id']);

        // update new category data
        $cat->update();

        // since a name change will change the object path, we must rebuild it here
        if ($oldCat->_objData['name'] != $cat->_objData['name']) {
            $obj = $cat->_objData;
            CategoryUtil::rebuildPaths('path', 'name', $obj['id']);
        }

        $msg = __f('Done! Saved the %s category.', $oldCat->_objData['name']);
        LogUtil::registerStatus($msg);
        $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    /**
     * create category
     */
    public function newcat()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $cat = new Categories_DBObject_Category ();
        $cat->getDataFromInput();

        // submit button wasn't pressed -> category was chosen from dropdown
        // we now get the parent (security) category domains so we can inherit them
        if (!FormUtil::getPassedValue('category_submit', null, 'POST')) {
            $newCat = $_POST['category'];
            $pcID = $newCat['parent_id'];

            $pCat = new Categories_DBObject_Category ();
            $parentCat = $pCat->get($pcID);

            //$newCat['security_domain'] = $parentCat['security_domain'];
            //for ($i=1; $i<=5; $i++) {
            //    $name = 'data' . $i . '_domain';
            //    $newCat[$name] = $parentCat[$name];
            //}

            $_SESSION['newCategory'] = $newCat;

            return System::redirect(ModUtil::url('Categories', 'admin', 'newcat') . '#top');
        }

        if (!$cat->validate('admin')) {
            return System::redirect(ModUtil::url('Categories', 'admin', 'newcat') . '#top');
        }

        $attributes = array();
        $values = FormUtil::getPassedValue('attribute_value', array(), 'POST');
        foreach (FormUtil::getPassedValue('attribute_name', array(), 'POST') as $index => $name) {
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
        $this->redirect(ModUtil::url('Categories', 'admin', 'view') . '#top');
    }

    /**
     * delete category
     */
    public function delete()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        if (FormUtil::getPassedValue('category_cancel', null, 'POST')) {
            return System::redirect(ModUtil::url('Categories', 'admin', 'view'));
        }

        $cid = FormUtil::getPassedValue('cid', null, 'POST');
        $cat = new Categories_DBObject_Category ();
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
        $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    /**
     * copy category
     */
    public function copy()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        if (FormUtil::getPassedValue('category_cancel', null, 'POST')) {
            return System::redirect(ModUtil::url('Categories', 'admin', 'view'));
        }

        $cid = FormUtil::getPassedValue('cid', null, 'POST');
        $cat = new Categories_DBObject_Category ();
        $cat->get($cid);

        $cat->copy($_POST['category']['parent_id']);

        $msg = __f('Done! Copied the %s category.', $cat->_objData['name']);
        LogUtil::registerStatus($msg);
        $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    /**
     * move category
     */
    public function move()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        if (FormUtil::getPassedValue('category_cancel', null, 'POST')) {
            return System::redirect(ModUtil::url('Categories', 'admin', 'view'));
        }

        $cid = FormUtil::getPassedValue('cid', null, 'POST');
        $cat = new Categories_DBObject_Category ();
        $cat->get($cid);
        $cat->move($_POST['category']['parent_id']);

        $msg = __f('Done! Moved the %s category.', $cat->_objData['name']);
        LogUtil::registerStatus($msg);
        $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    /**
     * rebuild path structure
     */
    public function rebuild_paths()
    {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        CategoryUtil::rebuildPaths('path', 'name');
        CategoryUtil::rebuildPaths('ipath', 'id');

        LogUtil::registerStatus(__('Done! Rebuilt the category paths.'));
        $this->redirect(ModUtil::url('Categories', 'admin', 'view'));
    }

    public function editregistry()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $id = FormUtil::getPassedValue('id', 0);

        $class = 'Categories_DBObject_Registry';

        if (FormUtil::getPassedValue('mode', null, 'POST') == 'delete') {
            $obj = new $class();
            $obj->get($id);
            $obj->delete($id);

            LogUtil::registerStatus(__('Done! Deleted the category registry entry.'));

            return System::redirect(ModUtil::url('Categories', 'admin', 'editregistry'));
        }

        $args = array();
        if (!FormUtil::getPassedValue('category_submit', null, 'POST')) { // got here through selector auto-submit
            $obj = new $class();
            $data = $obj->getDataFromInput($id);
            $args['category_registry'] = $data;

            return System::redirect(ModUtil::url('Categories', 'admin', 'editregistry', $args));
        }

        $obj = new $class();
        $obj->getDataFromInput();

        if (!$obj->validate('admin')) {
            return System::redirect(ModUtil::url('Categories', 'admin', 'editregistry'));
        }

        $obj->save();
        LogUtil::registerStatus(__('Done! Saved the category registry entry.'));
        $this->redirect(ModUtil::url('Categories', 'admin', 'editregistry'));
    }

    public function preferences()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $userrootcat = FormUtil::getPassedValue('userrootcat', null);
        if ($userrootcat) {
            $this->setVar('userrootcat', $userrootcat);
        }

        $autocreateusercat = (int)FormUtil::getPassedValue('autocreateusercat', 0);
        $this->setVar('autocreateusercat', $autocreateusercat);

        $allowusercatedit = (int)FormUtil::getPassedValue('allowusercatedit', 0);
        $this->setVar('allowusercatedit', $allowusercatedit);

        $autocreateuserdefaultcat = FormUtil::getPassedValue('autocreateuserdefaultcat', 0);
        $this->setVar('autocreateuserdefaultcat', $autocreateuserdefaultcat);

        $userdefaultcatname = FormUtil::getPassedValue('userdefaultcatname', 'Default');
        $this->setVar('userdefaultcatname', $userdefaultcatname);

        $permissionsall = (int)FormUtil::getPassedValue('permissionsall', 0);
        $this->setVar('permissionsall', $permissionsall);

        LogUtil::registerStatus(__('Done! Saved module configuration.'));
        $this->redirect(ModUtil::url('Categories', 'admin', 'preferences'));
    }

}

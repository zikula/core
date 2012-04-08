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

class Categories_Controller_UserformController extends Zikula_AbstractController
{
    /**
     * delete category
     */
    public function deleteAction()
    {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        $cid = (int)$this->request->get('cid', 0);
        $dr = (int)$this->request->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            return LogUtil::registerError($this->__('Error! The document root is invalid.'), null, $url);
        }

        if (!$cid) {
            return LogUtil::registerError($this->__('Error! The category ID is invalid.'), null, $url);
        }

        $obj = new Categories_DBObject_Category ();
        $data = $obj->get($cid);

        if (!$data) {
            $msg = $this->__f('Error! Cannot retrieve category with ID %s.', $cid);
            return LogUtil::registerError($msg, null, $url);
        }

        if ($data['is_locked']) {
            //! %1$s is the id, %2$s is the name
            return LogUtil::registerError($this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($cid, $data['name'])), null, $url);
        }

        CategoryUtil::deleteCategoryByID($cid);
        return $this->redirect($url);
    }

    /**
     * update category
     */
    public function editAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $dr = (int)$this->request->request->get('dr', 0);
        $ref = System::serverGetVar('HTTP_REFERER');

        $returnfunc = strpos($ref, "useredit") !== false ? 'useredit' : 'edit';
        $url = ModUtil::url('Categories', 'user', $returnfunc, array('dr' => $dr));

        if (!$dr) {
            return LogUtil::registerError($this->__('Error! The document root is invalid.'), null, $url);
        }

        $obj = new Categories_DBObject_Category ();
        $data = $obj->getDataFromInput();
        $oldData = $obj->get($data['id']);
        $obj->setData($data);

        if (!$oldData) {
            $msg = $this->__f('Error! Cannot retrieve category with ID %s.', $data['id']);
            return LogUtil::registerError($msg, null, $url);
        }

        if ($oldData['is_locked']) {
            //! %1$s is the id, %2$s is the name
            return LogUtil::registerError($this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($data['id'], $oldData['name'])), null, $url);
        }

        if (!$obj->validate()) {
            $_POST['cid'] = (int)$_POST['category']['id'];
            return $this->redirect(ModUtil::url('Categories', 'user', 'edit', $_POST) . '#top');
        }

        $attributes = array();
        $values = $this->request->request->get('attribute_value');
        foreach ($this->request->request->get('attribute_name') as $index => $name) {
            if (!empty($name)) $attributes[$name] = $values[$index];
        }

        $obj->setDataField('__ATTRIBUTES__', $attributes);

        // update new category data
        $obj->update();

        // since a name change will change the object path, we must rebuild it here
        if ($oldData['name'] != $data['name']) {
            CategoryUtil::rebuildPaths('path', 'name', $data['id']);
        }

        $msg = $this->__f('Done! Saved the %s category.', $oldData['name']);
        LogUtil::registerStatus($msg);
        return $this->redirect($url);
    }

    /**
     * move field
     */
    public function moveFieldAction()
    {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $cid = (int)$this->request->query->get('cid', 0);
        $dir = $this->request->query->get('direction', null);
        $dr = (int)$this->request->query->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            return LogUtil::registerError($this->__('Error! The document root is invalid.'), null, $url);
        }

        if (!$cid) {
            return LogUtil::registerError($this->__('Error! The category ID is invalid.'), null, $url);
        }

        if (!$dir) {
            return LogUtil::registerError($this->__f('Error! Invalid [%s] received.', 'direction'), null, $url);
        }

        $cats = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats = CategoryUtil::resequence($cats, 10);
        $ak = array_keys($cats);
        foreach ($ak as $k) {
            $obj = new Categories_DBObject_Category($cats[$k]);
            $obj->update();
        }

        $data = array('id' => $cid);
        $val = ObjectUtil::moveField($data, 'categories_category', $dir, 'sort_value');

        $url = System::serverGetVar('HTTP_REFERER');
        return $this->redirect($url);
    }

    /**
     * create category
     */
    public function newcatAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $dr = (int)$this->request->request->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            return LogUtil::registerError($this->__('Error! The document root is invalid.'), null, $url);
        }

        $cat = new Categories_DBObject_Category ();
        $data = $cat->getDataFromInput();

        if (!$cat->validate()) {
            return $this->redirect(ModUtil::url('Categories', 'user', 'edit', $_POST) . '#top');
        }

        $cat->insert();
        // since the original insert can't construct the ipath (since
        // the insert id is not known yet) we update the object here.
        $cat->update();

        $msg = $this->__f('Done! Inserted the %s category.', $data['name']);
        LogUtil::registerStatus($msg);
        return $this->redirect($url);
    }

    /**
     * resequence categories
     */
    public function resequenceAction()
    {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $dr = (int)$this->request->query->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            return LogUtil::registerError($this->__('Error! The document root is invalid.'), null, $url);
        }

        $cats = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats = CategoryUtil::resequence($cats, 10);

        $ak = array_keys($cats);
        foreach ($ak as $k) {
            $obj = new Categories_DBObject_Category($cats[$k]);
            $obj->update();
        }

        return $this->redirect(System::serverGetVar('HTTP_REFERER'));
    }

}
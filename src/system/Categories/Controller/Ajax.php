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
 * Categories_Controller_Ajax.
 */
class Categories_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * Resequence categories
     */
    public function resequence()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT));

        $data  = json_decode($this->request->request->get('data'), true);
        $cats = CategoryUtil::getSubCategories(1, true, true, true, true, true, '', 'id');

        foreach ($cats as $k => $cat) {
            $cid = $cat['id'];
            if (isset($data[$cid])) {
                $cats[$k]['sort_value'] = $data[$cid]['lineno'];
                $cats[$k]['parent_id'] = $data[$cid]['parent'];
                $obj = new Categories_DBObject_Category($cats[$k]);
                $obj->update();
            }
        }

        $result = array(
            'response' => true
        );

        return new Zikula_Response_Ajax($result);
    }

    public function edit($args = array())
    {
        $this->checkAjaxToken();

        $mode = $this->request->request->get('mode', 'new');
        $accessLevel = $mode == 'edit' ? ACCESS_EDIT : ACCESS_ADD;
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Categories::', '::', $accessLevel));

        $cid = isset($args['cid']) ? $args['cid'] : $this->request->request->get('cid', 0);
        $parent = isset($args['parent']) ? $args['parent'] : $this->request->request->get('parent', 1);
        $validationErrors = FormUtil::getValidationErrors();
        $editCat = '';

        $languages = ZLanguage::getInstalledLanguages();

        if ($validationErrors) {
            $category = new Categories_DBObject_Category(DBObject::GET_FROM_VALIDATION_FAILED); // need this for validation info
            $editCat = $category->get();
            $validationErrors = $validationErrors['category'];
        } else {
            // indicates that we're editing
            if ($mode == 'edit') {
                if (!$cid) {
                    return new Zikula_Response_Ajax_BadData($this->__('Error! Cannot determine valid \'cid\' for edit mode in \'Categories_admin_edit\'.'));
                }
                $category = new Categories_DBObject_Category();
                $editCat = $category->select($cid);
                $this->throwNotFoundUnless($editCat, $this->__('Sorry! No such item found.'));
            } else {
                // someone just pressen 'new' -> populate defaults
                $category = new Categories_DBObject_Category(); // need this for validation info
                $editCat['sort_value'] = '0';
                $editCat['parent_id'] = $parent;
            }
        }

        $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : array();

        Zikula_AbstractController::configureView();
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        $this->view->assign('mode', $mode)
            ->assign('category', $editCat)
            ->assign('attributes', $attributes)
            ->assign('languages', $languages)
            ->assign('validation', $category->_objValidation);

        $result = array(
            'action' => $mode == 'new' ? 'add' : 'edit',
            'result' => $this->view->fetch('categories_adminajax_edit.tpl'),
            'validationErrors' => $validationErrors
        );
        if ($validationErrors) {
            return new Zikula_Response_Ajax_BadData($validationErrors, $result);
        }

        return new Zikula_Response_Ajax($result);
    }

    public function copy()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD));

        $cid = $this->request->request->get('cid');
        $parent = $this->request->request->get('parent');

        $cat = new Categories_DBObject_Category(DBObject::GET_FROM_DB, $cid);
        $cat->copy($parent);
        $copyParent = new Categories_DBObject_Category(DBObject::GET_FROM_DB, $cat->getDataField('parent_id'));

        $categories = CategoryUtil::getSubCategories($copyParent->getDataField('id'), true, true, true, true, true);
        $options = array(
            'nullParent' => $copyParent->getDataField('parent_id'),
            'withWraper' => false,
        );

        $node = CategoryUtil::getCategoryTreeJS((array)$categories, true, true, $options);

        $leafStatus = array(
            'leaf' => array(),
            'noleaf' => array()
        );
        foreach ($categories as $c) {
            if ($c['is_leaf']) {
                $leafStatus['leaf'][] = $c['id'];
            } else {
                $leafStatus['noleaf'][] = $c['id'];
            }
        }
        $result = array(
            'action' => 'copy',
            'cid' => $cid,
            'copycid' => $copyParent->getDataField('id'),
            'parent' => $copyParent->getDataField('parent_id'),
            'node' => $node,
            'leafstatus' => $leafStatus,
            'result' => true
        );

        return new Zikula_Response_Ajax($result);
    }

    public function delete()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Categories::', '::', ACCESS_DELETE));

        $cid = $this->request->request->get('cid');
        $cat = new Categories_DBObject_Category(DBObject::GET_FROM_DB, $cid);
        $cat->delete(true);

        $result = array(
            'action' => 'delete',
            'cid' => $cid,
            'result' => true
        );

        return new Zikula_Response_Ajax($result);
    }

    public function deleteandmovesubs()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Categories::', '::', ACCESS_DELETE));

        $cid = $this->request->request->get('cid');
        $parent = $this->request->request->get('parent');
        $cat = new Categories_DBObject_Category(DBObject::GET_FROM_DB, $cid);
        $cat->deleteMoveSubcategories($parent);
        // need to re-render new parents node

        $newParent = new Categories_DBObject_Category(DBObject::GET_FROM_DB, $parent);
        $categories = CategoryUtil::getSubCategories($newParent->getDataField('id'), true, true, true, true, true);
        $options = array(
            'nullParent' => $newParent->getDataField('parent_id'),
            'withWraper' => false,
        );
        $node = CategoryUtil::getCategoryTreeJS((array)$categories, true, true, $options);

        $leafStatus = array(
            'leaf' => array(),
            'noleaf' => array()
        );
        foreach ($categories as $c) {
            if ($c['is_leaf']) {
                $leafStatus['leaf'][] = $c['id'];
            } else {
                $leafStatus['noleaf'][] = $c['id'];
            }
        }

        $result = array(
            'action' => 'deleteandmovesubs',
            'cid' => $cid,
            'parent' => $newParent->getDataField('id'),
            'node' => $node,
            'leafstatus' => $leafStatus,
            'result' => true
        );

        return new Zikula_Response_Ajax($result);
    }

    public function deletedialog()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Categories::', '::', ACCESS_DELETE));

        $cid = $this->request->request->get('cid');
        $allCats = CategoryUtil::getSubCategories(1, true, true, true, false, true, $cid);
        $selector = CategoryUtil::getSelector_Categories($allCats);

        Zikula_AbstractController::configureView();
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        $this->view->assign('categorySelector', $selector);
        $result = array(
            'result' => $this->view->fetch('categories_adminajax_delete.tpl'),
        );

        return new Zikula_Response_Ajax($result);
    }

    public function activate()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT));

        $cid = $this->request->request->get('cid');
        $cat = new Categories_DBObject_Category(DBObject::GET_FROM_DB, $cid);
        $cat->setDataField('status', 'A');
        $cat->update();

        $result = array(
            'action' => 'activate',
            'cid' => $cid,
            'result' => true
        );

        return new Zikula_Response_Ajax($result);
    }

    public function deactivate()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT));

        $cid = $this->request->request->get('cid');
        $cat = new Categories_DBObject_Category(DBObject::GET_FROM_DB, $cid);
        $cat->setDataField('status', 'I');
        $cat->update();

        $result = array(
            'action' => 'deactivate',
            'cid' => $cid,
            'result' => true
        );

        return new Zikula_Response_Ajax($result);
    }

    public function save()
    {
        $this->checkAjaxToken();
        $mode = $this->request->request->get('mode', 'new');
        $accessLevel = $mode == 'edit' ? ACCESS_EDIT : ACCESS_ADD;
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Categories::', '::', $accessLevel));

        $result = array();

        $cat = new Categories_DBObject_Category();
        $cat->getDataFromInput();

        if (!$cat->validate()) {
            $args = array(
                'cid' => $cat->getDataField('id'),
                'parent' => $cat->getDataField('parent_id'),
                'mode' => $mode
            );

            return $this->edit($args);
        }

        $attributes = array();
        $values = $this->request->request->get('attribute_value');
        foreach ($this->request->request->get('attribute_name') as $index => $name) {
            if (!empty($name)) {
                $attributes[$name] = $values[$index];
            }
        }

        $cat->setDataField('__ATTRIBUTES__', $attributes);

        if ($mode == 'edit') {
            // retrieve old category from DB
            $category = $this->request->request->get('category');
            $oldCat = new Categories_DBObject_Category(DBObject::GET_FROM_DB, $category['id']);

            // update new category data
            $cat->update();

            // since a name change will change the object path, we must rebuild it here
            if ($oldCat->getDataField('name') != $cat->getDataField('name')) {
                CategoryUtil::rebuildPaths('path', 'name', $cat->getDataField('id'));
            }
        } else {
            $cat->insert();
            // update new category data
            $cat->update();
        }

        $categories = CategoryUtil::getSubCategories($cat->getDataField('id'), true, true, true, true, true);
        $options = array(
            'nullParent' => $cat->getDataField('parent_id'),
            'withWraper' => false,
        );
        $node = CategoryUtil::getCategoryTreeJS((array)$categories, true, true, $options);

        $leafStatus = array(
            'leaf' => array(),
            'noleaf' => array()
        );
        foreach ($categories as $c) {
            if ($c['is_leaf']) {
                $leafStatus['leaf'][] = $c['id'];
            } else {
                $leafStatus['noleaf'][] = $c['id'];
            }
        }

        $result = array(
            'action' => $mode == 'edit' ? 'edit' : 'add',
            'cid' => $cat->getDataField('id'),
            'parent' => $cat->getDataField('parent_id'),
            'node' => $node,
            'leafstatus' => $leafStatus,
            'result' => true
        );

        return new Zikula_Response_Ajax($result);
    }

}

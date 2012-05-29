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

namespace CategoriesModule\Controller;

use SecurityUtil, ModUtil, LogUtil, CategoryUtil, UserUtil, ZLanguage, FormUtil;
use StringUtil, System, Zikula_View;

class UserformController extends \Zikula\Framework\Controller\AbstractController
{
    /**
     * delete category
     */
    public function deleteAction()
    {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_DELETE)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
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

        $category = \CategoryUtil::getCategoryByID($cid);

        if (!$category) {
            $msg = $this->__f('Error! Cannot retrieve category with ID %s.', $cid);
            return LogUtil::registerError($msg, null, $url);
        }

        if ($category['is_locked']) {
            //! %1$s is the id, %2$s is the name
            return LogUtil::registerError($this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($cid, $category['name'])), null, $url);
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
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        $dr = (int)$this->request->request->get('dr', 0);
        $ref = System::serverGetVar('HTTP_REFERER');

        $returnfunc = strpos($ref, "useredit") !== false ? 'useredit' : 'edit';
        $url = ModUtil::url('Categories', 'user', $returnfunc, array('dr' => $dr));

        if (!$dr) {
            return LogUtil::registerError($this->__('Error! The document root is invalid.'), null, $url);
        }

        // get data from post
        $data = $this->request->request->get('category', null);

        $valid = \CategoriesModule\GenericUtil::validateCategoryData($data);
        if (!$valid) {
            return $this->redirect($url);
        }

        // process name
        $data['name'] = \CategoriesModule\GenericUtil::processCategoryName($data['name']);

        // process parent
        $data['parent'] = \CategoriesModule\GenericUtil::processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = \CategoriesModule\GenericUtil::processCategoryDisplayName($data['display_name'], $data['name']);

        // get existing category
        $category = $this->entityManager->find('Zikula\Core\Doctrine\Entity\Category', $data['id']);

        if (!$category) {
            $msg = $this->__f('Error! Cannot retrieve category with ID %s.', $data['id']);
            return LogUtil::registerError($msg, null, $url);
        }

        if ($category['is_locked']) {
            return LogUtil::registerError($this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($data['id'], $category['name'])), null, $url);
        }

        $category_old_name = $category['name'];

        // save category
        $category->merge($data);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // process path and ipath
        $category['path'] = \CategoriesModule\GenericUtil::processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = \CategoriesModule\GenericUtil::processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $this->request->request->get('attribute_name', array());
        $attrib_values = $this->request->request->get('attribute_value', array());
        \CategoriesModule\GenericUtil::processCategoryAttributes($category, $attrib_names, $attrib_values);

        $this->entityManager->flush();

        if ($category_old_name != $category['name']) {
            CategoryUtil::rebuildPaths('path', 'name', $category['id']);
        }

        $msg = $this->__f('Done! Saved the %s category.', $category_old_name);
        LogUtil::registerStatus($msg);
        return $this->redirect($url);
    }

    /**
     * move field
     */
    public function moveFieldAction()
    {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            throw new \Zikula\Framework\Exception\ForbiddenException();
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

        $cats1 = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats2 = CategoryUtil::resequence($cats1, 10);

        $sort_values = array();

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $this->entityManager->find('Zikula\Core\Doctrine\Entity\Category', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
            $sort_values[] = array('id' => $obj['id'], 'sort_value' => $obj['sort_value']);
        }

        $this->entityManager->flush();

        $obj = $this->entityManager->find('Zikula\Core\Doctrine\Entity\Category', $cid);

        for ($i=0 ; $i < count($sort_values) ; $i++) {
            if ($sort_values[$i]['id'] == $cid) {
                if ($dir == 'up') {
                    if ($sort_values[$i-1]['sort_value']) {
                        $obj['sort_value'] = $sort_values[$i-1]['sort_value'] - 1;
                    }
                } else {
                    if ($sort_values[$i+1]['sort_value']) {
                        $obj['sort_value'] = $sort_values[$i+1]['sort_value'] + 1;
                    }
                }
            }
        }

        $this->entityManager->flush();

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
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        $dr = (int)$this->request->request->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            return LogUtil::registerError($this->__('Error! The document root is invalid.'), null, $url);
        }

        // get data from post
        $data = $this->request->request->get('category', null);

        $valid = \CategoriesModule\GenericUtil::validateCategoryData($data);
        if (!$valid) {
            return $this->redirect(ModUtil::url('Categories', 'user', 'edit', array('dr' => $dr)));
        }

        // process name
        $data['name'] = \CategoriesModule\GenericUtil::processCategoryName($data['name']);

        // process parent
        $data['parent'] = \CategoriesModule\GenericUtil::processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = \CategoriesModule\GenericUtil::processCategoryDisplayName($data['display_name'], $data['name']);

        // process sort value
        $data['sort_value'] = 0;

        // save category
        $category = new \Zikula\Core\Doctrine\Entity\Category;
        $category->merge($data);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // process path and ipath
        $category['path'] = \CategoriesModule\GenericUtil::processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = \CategoriesModule\GenericUtil::processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $this->request->request->get('attribute_name', array());
        $attrib_values = $this->request->request->get('attribute_value', array());
        \CategoriesModule\GenericUtil::processCategoryAttributes($category, $attrib_names, $attrib_values);

        $this->entityManager->flush();

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
            throw new \Zikula\Framework\Exception\ForbiddenException();
        }

        $dr = (int)$this->request->query->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            return LogUtil::registerError($this->__('Error! The document root is invalid.'), null, $url);
        }

        $cats1 = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats2 = CategoryUtil::resequence($cats1, 10);

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $this->entityManager->find('Zikula\Core\Doctrine\Entity\Category', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
        }

        $this->entityManager->flush();

        return $this->redirect($url);
    }

}
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

use LogUtil;
use SecurityUtil;
use System;
use CategoryUtil;
use ModUtil;
use ObjectUtil;
use Zikula\Module\CategoriesModuleGenericUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * User form contollers for the categories module
 */
class UserformController extends \Zikula_AbstractController
{
    /**
     * delete category
     *
     * @return void
     *
     * @throws AccessDeniedException Thrown if the user doesn't have delete permissions over the module
     * @throws \InvalidArgumentException Thrown if the category or document root aren't supplied or are invalid
     * @throws \RuntimeException Thrown if the category is locked
     */
    public function deleteAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $cid = (int)$this->request->get('cid', 0);
        $dr = (int)$this->request->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        if (!$cid) {
            throw new \InvalidArgumentException($this->__('Error! The category ID is invalid.'));
        }

        $category = CategoryUtil::getCategoryByID($cid);

        if (!$category) {
            throw new \InvalidArgumentException($this->__f('Error! Cannot retrieve category with ID %s.', $cid));
        }

        if ($category['is_locked']) {
            //! %1$s is the id, %2$s is the name
            throw new \RuntimeException($this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($cid, $category['name'])), null, $url);
        }

        CategoryUtil::deleteCategoryByID($cid);
        return $this->redirect($url);
    }

    /**
     * update category
     *
     * @return void
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     * @throws \InvalidArgumentException Thrown if the document root is invalid or
     *                                          if the category id doesn't match a valid category
     * @throws \RuntimeException Thrown if the category is locked
     */
    public function editAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $dr = (int)$this->request->request->get('dr', 0);
        $ref = System::serverGetVar('HTTP_REFERER');

        $returnfunc = strpos($ref, "useredit") !== false ? 'useredit' : 'edit';
        $url = ModUtil::url('ZikulaCategoriesModule', 'user', $returnfunc, array('dr' => $dr));

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        // get data from post
        $data = $this->request->request->get('category', null);

        $valid = GenericUtil::validateCategoryData($data);
        if (!$valid) {
            return $this->redirect($url);
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

        if (!$category) {
            throw new \InvalidArgumentException($this->__f('Error! Cannot retrieve category with ID %s.', $data['id']));
        }

        if ($category['is_locked']) {
            throw new \RuntimeException($this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($data['id'], $category['name'])));
        }

        $category_old_name = $category['name'];


        // save category
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

        if ($category_old_name != $category['name']) {
            CategoryUtil::rebuildPaths('path', 'name', $category['id']);
        }

        $msg = $this->__f('Done! Saved the %s category.', $category_old_name);
        LogUtil::registerStatus($msg);

        return $this->redirect($url);
    }

    /**
     * move field
     *
     * @return void
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     * @throws \InvalidArgumentException Thrown if the document root is invalid or
     *                                          if the category id doesn't match a valid category or
     *                                          if the direction is invalid
     */
    public function moveFieldAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $cid = (int)$this->request->query->get('cid', 0);
        $dir = $this->request->query->get('direction', null);
        $dr = (int)$this->request->query->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        if (!$cid) {
            throw new \InvalidArgumentException($this->__('Error! The category ID is invalid.'));
        }

        if (!$dir) {
            throw new \InvalidArgumentException($this->__f('Error! Invalid [%s] received.', 'direction'));
        }

        $cats1 = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats2 = CategoryUtil::resequence($cats1, 10);

        $sort_values = array();

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $this->entityManager->find('Zikula\Module\CategoriesModule\Entity\CategoryEntity', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
            $sort_values[] = array('id' => $obj['id'], 'sort_value' => $obj['sort_value']);
        }

        $this->entityManager->flush();

        $obj = $this->entityManager->find('Zikula\Module\CategoriesModule\Entity\CategoryEntity', $cid);

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
     *
     * @return void
     *
     * @throws AccessDeniedException Thrown if the user doesn't have add permissions to the module
     * @throws \InvalidArgumentException Thrown if the document root is invalid
     */
    public function newcatAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $dr = (int)$this->request->request->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        // get data from post
        $data = $this->request->request->get('category', null);

        $valid = GenericUtil::validateCategoryData($data);
        if (!$valid) {
            return $this->redirect(ModUtil::url('Categories', 'user', 'edit', array('dr' => $dr)));
        }

        // process name
        $data['name'] = GenericUtil::processCategoryName($data['name']);

        // process parent
        $data['parent'] = GenericUtil::processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = GenericUtil::processCategoryDisplayName($data['display_name'], $data['name']);

        // process sort value
        $data['sort_value'] = 0;

        // save category
        $category = new \Zikula\Module\CategoriesModule\Entity\CategoryEntity;
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

        $msg = $this->__f('Done! Inserted the %s category.', $data['name']);
        LogUtil::registerStatus($msg);
        return $this->redirect($url);
    }

    /**
     * resequence categories
     *
     * @return void
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     * @throws \InvalidArgumentException Thrown if the document root isn't valid
     */
    public function resequenceAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $dr = (int)$this->request->query->get('dr', 0);
        $url = System::serverGetVar('HTTP_REFERER');

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        $cats1 = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats2 = CategoryUtil::resequence($cats1, 10);

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $this->entityManager->find('Zikula\Module\CategoriesModule\Entity\CategoryEntity', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
        }

        $this->entityManager->flush();

        return $this->redirect($url);
    }
}
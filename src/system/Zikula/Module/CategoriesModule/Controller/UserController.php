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

use SecurityUtil;
use System;
use SessionUtil;
use Zikula_View;
use ModUtil;
use CategoryUtil;
use ZLanguage;
use UserUtil;
use ServiceUtil;
use LogUtil;
use Zikula\Module\CategoriesModule\CategoriesModuleInstaller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * User controllers for the categories module
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * main user function
     *
     * @return Response|RedirectResponse symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the module
     */
    public function mainAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $referer = System::serverGetVar('HTTP_REFERER');
        if (strpos($referer, 'module=ZikulaCategoriesModule') === false) {
            //$this->request->getSession()->set('categories_referer', $referer);
            SessionUtil::setVar('categories_referer', $referer);
        }

        $this->view->setCaching(\Zikula_View::CACHE_DISABLED);

        $allowed = $this->getVar('allowusercatedit', 0);
        if ($allowed) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'edituser')));
        } else {
            LogUtil::registerError($this->__("Sorry! User-owned category editing has not been enabled. This feature can be enabled by the site administrator."));
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }
    }

    /**
     * edit category for a simple, non-recursive set of categories
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the document root
     */
    public function editAction()
    {
        $docroot = $this->request->get('dr', 0);
        $cid = $this->request->get('cid', 0);
        $url = ModUtil::url('ZikulaCategoriesModule', 'user', 'edit', array('dr' => $docroot));

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', "ID::$docroot", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $referer = System::serverGetVar('HTTP_REFERER');
        if (strpos($referer, 'module=ZikulaCategoriesModule') === false) {
            //$this->request->getSession()->set('categories_referer', $referer);
            SessionUtil::setVar('categories_referer', $referer);
        }

        $rootCat = array();
        $allCats = array();
        $editCat = array();

        if (!$docroot) {
            LogUtil::registerError($this->__("Error! The URL contains an invalid 'document root' parameter."));
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }
        if ($docroot == 1) {
            LogUtil::registerError($this->__("Error! The root directory cannot be modified in 'user' mode"));
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        if (is_int((int)$docroot) && $docroot > 0) {
            $rootCat = CategoryUtil::getCategoryByID($docroot);
        } else {
            $rootCat = CategoryUtil::getCategoryByPath($docroot);
            if (!$rootCat) {
                $rootCat = CategoryUtil::getCategoryByPath($docroot, 'ipath');
            }
        }

        // now check if someone is trying edit another user's categories
        $userRoot = $this->getVar('userrootcat', 0);
        if ($userRoot) {
            $userRootCat = CategoryUtil::getCategoryByPath($userRoot);
            if ($userRootCat) {
                $userRootCatIPath = $userRootCat['ipath'];
                $rootCatIPath = $rootCat['ipath'];
                if (strpos($rootCatIPath, $userRootCatIPath) !== false) {
                    if (!SecurityUtil::checkPermission('Categories::category', "ID::$docroot", ACCESS_ADMIN)) {
                        $userRootCatPath = $userRootCat['path'];
                        $rootCatPath = $rootCat['path'];
                        if (strpos($rootCatPath, $userRootCatPath) === false) {
                            //! %s represents the root path (id), passed in the url
                            LogUtil::registerError($this->__f("Error! It looks like you are trying to edit another user's categories. Only site administrators can do that (%s).", $docroot));
                            return $this->response($this->view->fetch('User/editcategories.tpl'));
                        }
                    }
                }
            }
        }

        if ($cid) {
            $editCat = CategoryUtil::getCategoryByID($cid);
            if ($editCat['is_locked']) {
                //! %1$s is the id, %2$s is the name
                LogUtil::registerError($this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($cid, $editCat['name'])), null, $url);
                return $this->response($this->view->fetch('User/editcategories.tpl'));
            }
        }

        if (!$rootCat) {
            LogUtil::registerError($this->__f("Error! Cannot access root directory (%s).", $docroot), null, $url);
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }
        if ($editCat && !$editCat['is_leaf']) {
            LogUtil::registerError($this->__f('Error! The specified category is not a leaf-level category (%s).', $cid), null, $url);
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }
        if ($editCat && !CategoryUtil::isDirectSubCategory($rootCat, $editCat)) {
            LogUtil::registerError($this->__f('Error! The specified category is not a child of the document root (%1$s; %2$s).', array($docroot, $cid)), null, $url);
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        $allCats = CategoryUtil::getSubCategoriesForCategory($rootCat, false, false, false, true, true);

        $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : array();

        $languages = ZLanguage::getInstalledLanguages();

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        $this->view->assign('rootCat', $rootCat)
                   ->assign('category', $editCat)
                   ->assign('attributes', $attributes)
                   ->assign('allCats', $allCats)
                   ->assign('languages', $languages)
                   ->assign('userlanguage', ZLanguage::getLanguageCode())
                   ->assign('referer', \SessionUtil::getVar('categories_referer'));

        return $this->response($this->view->fetch('User/edit.tpl'));
    }

    /**
     * edit categories for the currently logged in user
     *
     * @return Response a symfony reponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over categories in the module or
     *                                                                                 if the user is not logged in
     */
    public function edituserAction()
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException($this->__('Error! Editing mode for user-owned categories is only available to users who have logged-in.'));
        }

        $allowUserEdit = $this->getVar('allowusercatedit', 0);
        if (!$allowUserEdit) {
            LogUtil::registerError($this->__('Error! User-owned category editing has not been enabled. This feature can be enabled by the site administrator.'));
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        $userRoot = $this->getVar('userrootcat', 0);
        if (!$userRoot) {
            LogUtil::registerError($this->__('Error! Could not determine the user root node.'));
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        $userRootCat = CategoryUtil::getCategoryByPath($userRoot);
        if (!$userRoot) {
            LogUtil::registerError($this->__f('Error! The user root node seems to point towards an invalid category: %s.', $userRoot));
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        if ($userRootCat == 1) {
            LogUtil::registerError($this->__("Error! The root directory cannot be modified in 'user' mode"));
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        $userCatName = $this->getusercategorynameAction();
        if (!$userCatName) {
            LogUtil::registerError($this->__('Error! Cannot determine user category root node name.'));
            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        $thisUserRootCatPath = $userRoot . '/' . $userCatName;
        $thisUserRootCat = CategoryUtil::getCategoryByPath($thisUserRootCatPath);

        $dr = null;
        if (!$thisUserRootCat) {
            $autoCreate = $this->getVar('autocreateusercat', 0);
            if (!$autoCreate) {
                LogUtil::registerError($this->__("Error! The user root category node for this user does not exist, and the automatic creation flag (autocreate) has not been set."));
                return $this->response($this->view->fetch('User/editcategories.tpl'));
            }

            $installer = new CategoriesInstaller($this->getContainer());

            $cat = array(
                'id' => '',
                'parent' => $this->entityManager->getReference('Zikula\Module\CategoriesModule\Entity\CategoryEntity', $userRootCat['id']),
                'name' => $userCatName,
                'display_name' => unserialize($installer->makeDisplayName($userCatName)),
                'display_desc' => unserialize($installer->makeDisplayDesc()),
                'path' => $thisUserRootCatPath,
                'status' => 'A'
            );

            $obj = new \Zikula\Module\CategoriesModule\Entity\CategoryEntity;
            $obj->merge($cat);
            $this->entityManager->persist($obj);
            $this->entityManager->flush();

            // since the original insert can't construct the ipath (since
            // the insert id is not known yet) we update the object here
            $obj->setIPath($userRootCat['ipath'] . '/' . $obj['id']);
            $this->entityManager->flush();

            $dr = $obj->getID();

            $autoCreateDefaultUserCat = $this->getVar('autocreateuserdefaultcat', 0);
            if ($autoCreateDefaultUserCat) {
                $userdefaultcatname = $this->getVar('userdefaultcatname', $this->__('Default'));
                $cat = array(
                    'id' => '',
                    'parent' => $this->entityManager->getReference('Zikula\Module\CategoriesModule\Entity\CategoryEntity', $dr),
                    'is_leaf' => 1,
                    'name' => $userdefaultcatname,
                    'sort_value' => 0,
                    'display_name' => unserialize($installer->makeDisplayName($userdefaultcatname)),
                    'display_desc' => unserialize($installer->makeDisplayDesc()),
                    'path' => $thisUserRootCatPath . '/' . $userdefaultcatname,
                    'status' => 'A'
                );

                $obj2 = new \Zikula\Module\CategoriesModule\Entity\CategoryEntity;
                $obj2->merge($cat);
                $this->entityManager->persist($obj2);
                $this->entityManager->flush();

                // since the original insert can't construct the ipath (since
                // the insert id is not known yet) we update the object here
                $obj2->setIPath($obj['ipath'] . '/' . $obj2['id']);
                $this->entityManager->flush();
            }
        } else {
            $dr = $thisUserRootCat['id'];
        }

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'user', 'edit', array('dr' => $dr))));
    }

    /**
     * refer the user back to the calling page
     *
     * @return void
     */
    public function referBackAction()
    {
        //$referer = $this->request->getSession()->get('categories_referer');
        //$this->request->getSession()->remove('categories_referer');
        $referer = SessionUtil::getVar('categories_referer');
        SessionUtil::DelVar('categories_referer');

        return new RedirectResponse(System::normalizeUrl($referer));
    }

    /**
     * return the categories for the currently logged in user, really only used for testing purposes
     *
     * @return array array of categories
     */
    public function getusercategoriesAction()
    {
        return ModUtil::apiFunc('ZikulaCategoriesModule', 'user', 'getusercategories');
    }

    /**
     * return the category name for a user, really only used for testing purposes
     *
     * @return string the username associated with the category
     */
    public function getusercategorynameAction()
    {
        return ModUtil::apiFunc('ZikulaCategoriesModule', 'user', 'getusercategoryname');
    }
}

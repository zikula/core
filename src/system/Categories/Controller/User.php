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

class Categories_Controller_User extends Zikula_AbstractController
{
    /**
     * main user function
     */
    public function main()
    {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $referer = System::serverGetVar('HTTP_REFERER');
        if (strpos($referer, 'module=Categories') === false) {
            SessionUtil::setVar('categories_referer', $referer);
        }

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        $this->view->assign('allowusercatedit', $this->getVar('allowusercatedit', 0));

        return $this->view->fetch('categories_user_editcategories.tpl');
    }

    /**
     * edit category for a simple, non-recursive set of categories
     */
    public function edit()
    {
        $docroot = FormUtil::getPassedValue('dr', 0);
        $cid = FormUtil::getPassedValue('cid', 0);
        $url = ModUtil::url('Categories', 'user', 'edit', array('dr' => $docroot));

        if (!SecurityUtil::checkPermission('Categories::category', "ID::$docroot", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError($url);
        }

        $referer = System::serverGetVar('HTTP_REFERER');
        if (strpos($referer, 'module=Categories') === false) {
            SessionUtil::setVar('categories_referer', $referer);
        }

        $rootCat = array();
        $allCats = array();
        $editCat = array();

        if (!$docroot) {
            return LogUtil::registerError($this->__("Error! The URL contains an invalid 'document root' parameter."), null, $url);
        }
        if ($docroot == 1) {
            return LogUtil::registerError($this->__("Error! The root directory cannot be modified in 'user' mode"), null, $url);
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
                        $thisUserRootCategoryName = ModUtil::apiFunc('Categories', 'user', 'getusercategoryname');
                        $thisUserRootCatPath = $userRootCat['path'] . '/' . $thisUserRootCategoryName;
                        $userRootCatPath = $userRootCat['path'];
                        $rootCatPath = $rootCat['path'];
                        if (strpos($rootCatPath, $userRootCatPath) === false) {
                            //! %s represents the root path (id), passed in the url
                            return LogUtil::registerError($this->__f("Error! It looks like you are trying to edit another user's categories. Only site administrators can do that (%s).", $docroot), null, $url);
                        }
                    }
                }
            }
        }

        if ($cid) {
            $editCat = CategoryUtil::getCategoryByID($cid);
            if ($editCat['is_locked']) {
                //! %1$s is the id, %2$s is the name
                return LogUtil::registerError($this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($cid, $editCat['name'])), null, $url);
            }
        }

        if (!$rootCat) {
            return LogUtil::registerError($this->__f("Error! Cannot access root directory (%s).", $docroot), null, $url);
        }
        if ($editCat && !$editCat['is_leaf']) {
            return LogUtil::registerError($this->__f('Error! The specified category is not a leaf-level category (%s).', $cid), null, $url);
        }
        if ($editCat && !CategoryUtil::isDirectSubCategory($rootCat, $editCat)) {
            return LogUtil::registerError($this->__f('Error! The specified category is not a child of the document root (%1$s; %2$s).', array($docroot, $cid)), null, $url);
        }

        $allCats = CategoryUtil::getSubCategoriesForCategory($rootCat, false, false, false, true, true);

        $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : array();

        $languages = ZLanguage::getInstalledLanguages();

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        return $this->view->assign('rootCat', $rootCat)
                    ->assign('category', $editCat)
                    ->assign('attributes', $attributes)
                    ->assign('allCats', $allCats)
                    ->assign('languages', $languages)
                    ->assign('userlanguage', ZLanguage::getLanguageCode())
                    ->assign('referer', SessionUtil::getVar('categories_referer'))
                    ->fetch('categories_user_edit.tpl');
    }

    /**
     * edit categories for the currently logged in user
     */
    public function edituser()
    {
        if (!SecurityUtil::checkPermission('Categories::category', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        if (!UserUtil::isLoggedIn()) {
            return LogUtil::registerError($this->__('Error! Editing mode for user-owned categories is only available to users who have logged-in.'));
        }

        $allowUserEdit = $this->getVar('allowusercatedit', 0);
        if (!$allowUserEdit) {
            return LogUtil::registerError($this->__('Error! User-owned category editing has not been enabled. This feature can be enabled by the site administrator.'));
        }

        $userRoot = $this->getVar('userrootcat', 0);
        if (!$userRoot) {
            return LogUtil::registerError($this->__('Error! Could not determine the user root node.'));
        }

        $userRootCat = CategoryUtil::getCategoryByPath($userRoot);
        if (!$userRoot) {
            return LogUtil::registerError($this->__f('Error! The user root node seems to point towards an invalid category: %s.', $userRoot));
        }

        if ($userRootCat == 1) {
            return LogUtil::registerError($this->__("Error! The root directory cannot be modified in 'user' mode"));
        }

        $userCatName = $this->getusercategoryname();
        if (!$userCatName) {
            return LogUtil::registerError($this->__('Error! Cannot determine user category root node name.'));
        }

        $thisUserRootCatPath = $userRoot . '/' . $userCatName;
        $thisUserRootCat = CategoryUtil::getCategoryByPath($thisUserRootCatPath);

        $dr = null;
        if (!$thisUserRootCat) {
            $autoCreate = $this->getVar('autocreateusercat', 0);
            if (!$autoCreate) {
                return LogUtil::registerError($this->__("Error! The user root category node for this user does not exist, and the automatic creation flag (autocreate) has not been set."));
            }

            $installer = new Categories_Installer(ServiceUtil::getManager());
            $cat = array('id' => '',
                    'parent_id' => $userRootCat['id'],
                    'name' => $userCatName,
                    'display_name' => unserialize($installer->makeDisplayName($userCatName)),
                    'display_desc' => unserialize($installer->makeDisplayDesc()),
                    'security_domain' => 'Categories::',
                    'path' => $thisUserRootCatPath,
                    'status' => 'A');

            $obj = new Categories_DBObject_Category();
            $obj->setData($cat);
            $obj->insert();
            // since the original insert can't construct the ipath (since
            // the insert id is not known yet) we update the object here
            $obj->update();
            $dr = $obj->getID();

            $autoCreateDefaultUserCat = $this->getVar('autocreateuserdefaultcat', 0);
            if ($autoCreateDefaultUserCat) {
                $userdefaultcatname = $this->getVar('userdefaultcatname', $this->__('Default'));
                $cat = array('id' => '',
                        'parent_id' => $dr,
                        'name' => $userdefaultcatname,
                        'display_name' => unserialize($installer->makeDisplayName($userdefaultcatname)),
                        'display_desc' => unserialize($installer->makeDisplayDesc()),
                        'security_domain' => 'Categories::',
                        'path' => $thisUserRootCatPath . '/' . $userdefaultcatname,
                        'status' => 'A');
                $obj->setData($cat);
                $obj->insert();
                // since the original insert can't construct the ipath (since
                // the insert id is not known yet) we update the object here
                $obj->update();
            }
        } else {
            $dr = $thisUserRootCat['id'];
        }

        $url = ModUtil::url('Categories', 'user', 'edit', array('dr' => $dr));
        $this->redirect($url);
    }

    /**
     * refer the user back to the calling page
     */
    public function referBack()
    {
        $referer = SessionUtil::getVar('categories_referer');
        SessionUtil::DelVar('categories_referer');
        $this->redirect($referer);
    }

    /**
     * return the categories for the currently logged in user, really only used for testing purposes
     */
    public function getusercategories()
    {
        return ModUtil::apiFunc('Categories', 'user', 'getusercategories');
    }

    /**
     * return the category name for a user, really only used for testing purposes
     */
    public function getusercategoryname()
    {
        return ModUtil::apiFunc('Categories', 'user', 'getusercategoryname');
    }
}

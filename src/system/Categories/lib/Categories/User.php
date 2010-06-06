<?php
/**
 * Zikula Application Framework
 *
 * @copyright Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 */

class Categories_User extends AbstractController
{

    /**
     * main user function
     */
    public function main()
    {
        if (!SecurityUtil::checkPermission('Categories::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $referer = System::serverGetVar ('HTTP_REFERER');
        if (strpos ($referer, 'module=Categories') === false) {
            SessionUtil::setVar('categories_referer', $referer);
        }

        $pnRender = Renderer::getInstance('Categories', false);
        $pnRender->assign ('allowusercatedit', ModUtil::getVar('Categories', 'allowusercatedit', 0));
        return $pnRender->fetch('categories_user_editcategories.htm');
    }

    /**
     * edit category for a simple, non-recursive set of categories
     */
    public function edit ()
    {
        $docroot = FormUtil::getPassedValue('dr', 0);
        $cid     = FormUtil::getPassedValue('cid', 0);
        $url     = ModUtil::url('Categories', 'user', 'edit', array('dr' => $docroot));

        if (!SecurityUtil::checkPermission('Categories::category', "ID::$docroot", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError($url);
        }

        $referer = System::serverGetVar ('HTTP_REFERER');
        if (strpos($referer, 'module=Categories') === false) {
            SessionUtil::setVar('categories_referer', $referer);
        }

        $rootCat = array();
        $allCats = array();
        $editCat = array();

        if (!$docroot) {
            return LogUtil::registerError(__("Error! The URL contains an invalid 'document root' parameter."), null, $url);
        }
        if ($docroot == 1) {
            return LogUtil::registerError(__("Error! The root directory cannot be modified in 'user' mode"), null, $url);
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
        $userRoot = ModUtil::getVar('Categories', 'userrootcat', 0);
        if ($userRoot) {
            $userRootCat = CategoryUtil::getCategoryByPath($userRoot);
            if ($userRootCat) {
                $userRootCatIPath = $userRootCat['ipath'];
                $rootCatIPath     = $rootCat['ipath'];
                if (strpos($rootCatIPath, $userRootCatIPath) !== false) {
                    if (!SecurityUtil::checkPermission('Categories::category', "ID::$docroot", ACCESS_ADMIN)) {
                        $thisUserRootCategoryName = ModUtil::apiFunc ('Categories', 'user', 'getusercategoryname');
                        $thisUserRootCatPath      = $userRootCat['path'] . '/' . $thisUserRootCategoryName;
                        $userRootCatPath          = $userRootCat['path'];
                        $rootCatPath              = $rootCat['path'];
                        if (strpos($rootCatPath, $userRootCatPath) === false) {
                            //! %s represents the root path (id), passed in the url
                            return LogUtil::registerError(__f("Error! It looks like you are trying to edit another user's categories. Only site administrators can do that (%s).", $docroot), null, $url);
                        }
                    }
                }
            }
        }

        if ($cid) {
            $editCat = CategoryUtil::getCategoryByID ($cid);
            if ($editCat['is_locked']) {
                //! %1$s is the id, %2$s is the name
                return LogUtil::registerError(__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($cid, $editCat['name'])), null, $url);
            }
        }

        if (!$rootCat) {
            return LogUtil::registerError(__f("Error! Cannot access root directory (%s).", $docroot), null, $url);
        }
        if ($editCat && !$editCat['is_leaf']) {
            return LogUtil::registerError(__f('Error! The specified category is not a leaf-level category (%s).', $cid), null, $url);
        }
        if ($editCat && !CategoryUtil::isDirectSubCategory ($rootCat, $editCat)) {
            return LogUtil::registerError(__f('Error! The specified category is not a child of the document root (%1$s; %2$s).', array($docroot, $cid)), null, $url);
        }

        $allCats = CategoryUtil::getSubCategoriesForCategory($rootCat, false, false, false, true, true);

        $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : array();

        $languages = ZLanguage::getInstalledLanguages();

        $pnRender = Renderer::getInstance('Categories', false);
        $pnRender->assign('rootCat', $rootCat);
        $pnRender->assign('category', $editCat);
        $pnRender->assign('attributes', $attributes);
        $pnRender->assign('allCats', $allCats);
        $pnRender->assign('languages', $languages);
        $pnRender->assign('userlanguage', ZLanguage::getLanguageCode());
        $pnRender->assign('referer', SessionUtil::getVar('categories_referer'));

        return $pnRender->fetch('categories_user_edit.htm');
    }

    /**
     * edit categories for the currently logged in user
     */
    public function edituser ()
    {
        if (!SecurityUtil::checkPermission('Categories::category', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        if (!UserUtil::isLoggedIn()) {
            return LogUtil::registerError(__('Error! Editing mode for user-owned categories is only available to users who have logged-in.'));
        }

        $allowUserEdit = ModUtil::getVar ('Categories', 'allowusercatedit', 0);
        if (!$allowUserEdit) {
            return LogUtil::registerError(__('Error! User-owned category editing has not been enabled. This feature can be enabled by the site administrator.'));
        }

        $userRoot = ModUtil::getVar('Categories', 'userrootcat', 0);
        if (!$userRoot) {
            return LogUtil::registerError(__('Error! Could not determine the user root node.'));
        }

        $userRootCat = CategoryUtil::getCategoryByPath($userRoot);
        if (!$userRoot) {
            return LogUtil::registerError(__f('Error! The user root node seems to point towards an invalid category: %s.', $userRoot));
        }

        if ($userRootCat == 1) {
            return LogUtil::registerError(__("Error! The root directory cannot be modified in 'user' mode"));
        }

        $userCatName = $this->getusercategoryname();
        if (!$userCatName) {
            return LogUtil::registerError(__('Error! Cannot determine user category root node name.'));
        }

        $thisUserRootCatPath = $userRoot . '/' . $userCatName;
        $thisUserRootCat = CategoryUtil::getCategoryByPath($thisUserRootCatPath);

        $dr = null;
        if (!$thisUserRootCat) {
            $autoCreate = ModUtil::getVar ('Categories', 'autocreateusercat', 0);
            if (!$autoCreate) {
                return LogUtil::registerError(__("Error! The user root category node for this user does not exist, and the automatic creation flag (autocreate) has not been set."));
            }

            require_once ('system/Categories/init.php'); // need this for Categories_makeDisplayName() && Categories_makeDisplayDesc()
            $cat = array('id'               => '',
                    'parent_id'        => $userRootCat['id'],
                    'name'             => $userCatName,
                    'display_name'     => unserialize(Categories_makeDisplayName($userCatName)),
                    'display_desc'     => unserialize(Categories_makeDisplayDesc()),
                    'security_domain'  => 'Categories::',
                    'path'             => $thisUserRootCatPath,
                    'status'           => 'A');

            $obj = new Categories_DBObject_Category();
            $obj->setData ($cat);
            $obj->insert ();
            // since the original insert can't construct the ipath (since
            // the insert id is not known yet) we update the object here
            $obj->update ();
            $dr = $obj->getID ();

            $autoCreateDefaultUserCat = ModUtil::getVar ('Categories', 'autocreateuserdefaultcat', 0);
            if ($autoCreateDefaultUserCat) {
                $userdefaultcatname = ModUtil::getVar ('Categories', 'userdefaultcatname', __('Default'));
                $cat = array('id'               => '',
                        'parent_id'        => $dr,
                        'name'             => $userdefaultcatname,
                        'display_name'     => unserialize(Categories_makeDisplayName($userdefaultcatname)),
                        'display_desc'     => unserialize(Categories_makeDisplayDesc()),
                        'security_domain'  => 'Categories::',
                        'path'             => $thisUserRootCatPath . '/' . $userdefaultcatname,
                        'status'           => 'A');
                $obj->setData ($cat);
                $obj->insert ();
                // since the original insert can't construct the ipath (since
                // the insert id is not known yet) we update the object here
                $obj->update ();
            }
        } else {
            $dr = $thisUserRootCat['id'];
        }

        $url = ModUtil::url ('Categories', 'user', 'edit', array('dr' => $dr));
        return System::redirect($url);
    }

    /**
     * refer the user back to the calling page
     */
    public function referBack()
    {
        $referer = SessionUtil::getVar ('categories_referer');
        SessionUtil::DelVar ('categories_referer');
        return System::redirect ($referer);
    }

    /**
     * return the categories for the currently logged in user, really only used for testing purposes
     */
    public function getusercategories ()
    {
        return ModUtil::apiFunc ('Categories', 'user', 'getusercategories');
    }

    /**
     * return the category name for a user, really only used for testing purposes
     */
    public function getusercategoryname ()
    {
        return ModUtil::apiFunc ('Categories', 'user', 'getusercategoryname');
    }

}
<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use SecurityUtil;
use System;
use SessionUtil;
use Zikula_View;
use ModUtil;
use CategoryUtil;
use ZLanguage;
use UserUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;

/**
 * User controllers for the categories module
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * @Route("")
     *
     * main user function
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the module
     */
    public function indexAction(Request $request)
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $referer = $request->server->get('HTTP_REFERER');
        if (strpos($referer, '/categories') === false) {
            //$request->getSession()->set('categories_referer', $referer);
            SessionUtil::setVar('categories_referer', $referer);
        }

        $this->view->setCaching(\Zikula_View::CACHE_DISABLED);

        $allowed = $this->getVar('allowusercatedit', 0);
        if ($allowed) {
            return new RedirectResponse($this->get('router')->generate('zikulacategoriesmodule_user_edituser', array(), RouterInterface::ABSOLUTE_URL));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__("Sorry! User-owned category editing has not been enabled. This feature can be enabled by the site administrator."));

            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }
    }

    /**
     * Route not needed here because method is legacy-only
     *
     * legacy main user function
     *
     * @deprecated since 1.4.0 @see indexAction()
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        return new RedirectResponse($this->get('router')->generate('zikulacategoriesmodule_admin_index', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/edit")
     *
     * edit category for a simple, non-recursive set of categories
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the document root
     */
    public function editAction(Request $request)
    {
        $docroot = $request->get('dr', 0);
        $cid = $request->get('cid', 0);
        $url = $this->get('router')->generate('zikulacategoriesmodule_user_edit', array('dr' => $docroot), RouterInterface::ABSOLUTE_URL);

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', "ID::$docroot", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $referer = System::serverGetVar('HTTP_REFERER');
        if (strpos($referer, 'module=ZikulaCategoriesModule') === false) {
            //$request->getSession()->set('categories_referer', $referer);
            SessionUtil::setVar('categories_referer', $referer);
        }

        $editCat = array();

        if (!$docroot) {
            $request->getSession()->getFlashBag()->add('error', $this->__("Error! The URL contains an invalid 'document root' parameter."));

            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }
        if ($docroot == 1) {
            $request->getSession()->getFlashBag()->add('error', $this->__("Error! The root directory cannot be modified in 'user' mode"));

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
                            $request->getSession()->getFlashBag()->add('error', $this->__f("Error! It looks like you are trying to edit another user's categories. Only site administrators can do that (%s).", $docroot));

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
                $request->getSession()->getFlashBag()->add('error', $this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', array($cid, $editCat['name'])), null, $url);

                return $this->response($this->view->fetch('User/editcategories.tpl'));
            }
        }

        if (!$rootCat) {
            $request->getSession()->getFlashBag()->add('error', $this->__f("Error! Cannot access root directory (%s).", $docroot), null, $url);

            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }
        if ($editCat && !$editCat['is_leaf']) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! The specified category is not a leaf-level category (%s).', $cid), null, $url);

            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }
        if ($editCat && !CategoryUtil::isDirectSubCategory($rootCat, $editCat)) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! The specified category is not a child of the document root (%1$s; %2$s).', array($docroot, $cid)), null, $url);

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
     * @Route("/edituser")
     *
     * edit categories for the currently logged in user
     *
     * @param Request $request
     *
     * @return Response a symfony reponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over categories in the module or
     *                                                                                 if the user is not logged in
     */
    public function edituserAction(Request $request)
    {
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException($this->__('Error! Editing mode for user-owned categories is only available to users who have logged-in.'));
        }

        $allowUserEdit = $this->getVar('allowusercatedit', 0);
        if (!$allowUserEdit) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! User-owned category editing has not been enabled. This feature can be enabled by the site administrator.'));

            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        $userRoot = $this->getVar('userrootcat', 0);
        if (!$userRoot) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! Could not determine the user root node.'));

            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        $userRootCat = CategoryUtil::getCategoryByPath($userRoot);
        if (!$userRoot) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! The user root node seems to point towards an invalid category: %s.', $userRoot));

            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        if ($userRootCat == 1) {
            $request->getSession()->getFlashBag()->add('error', $this->__("Error! The root directory cannot be modified in 'user' mode"));

            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        $userCatName = $this->getusercategorynameAction();
        if (!$userCatName) {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! Cannot determine user category root node name.'));

            return $this->response($this->view->fetch('User/editcategories.tpl'));
        }

        $thisUserRootCatPath = $userRoot . '/' . $userCatName;
        $thisUserRootCat = CategoryUtil::getCategoryByPath($thisUserRootCatPath);

        $dr = null;
        if (!$thisUserRootCat) {
            $autoCreate = $this->getVar('autocreateusercat', 0);
            if (!$autoCreate) {
                $request->getSession()->getFlashBag()->add('error', $this->__("Error! The user root category node for this user does not exist, and the automatic creation flag (autocreate) has not been set."));

                return $this->response($this->view->fetch('User/editcategories.tpl'));
            }

            $cat = array(
                'id' => '',
                'parent' => $this->entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $userRootCat['id']),
                'name' => $userCatName,
                'display_name' => array(ZLanguage::getLanguageCode() => $userCatName),
                'display_desc' => array(ZLanguage::getLanguageCode() => ''),
                'path' => $thisUserRootCatPath,
                'status' => 'A'
            );

            $obj = new \Zikula\CategoriesModule\Entity\CategoryEntity();
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
                    'parent' => $this->entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $dr),
                    'is_leaf' => 1,
                    'name' => $userdefaultcatname,
                    'sort_value' => 0,
                    'display_name' => array(ZLanguage::getLanguageCode() => $userdefaultcatname),
                    'display_desc' => array(ZLanguage::getLanguageCode() => ''),
                    'path' => $thisUserRootCatPath . '/' . $userdefaultcatname,
                    'status' => 'A'
                );

                $obj2 = new \Zikula\CategoriesModule\Entity\CategoryEntity();
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

        return new RedirectResponse($this->get('router')->generate('zikulacategoriesmodule_user_edit', array('dr' => $dr), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/refer")
     *
     * refer the user back to the calling page
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function referBackAction(Request $request)
    {
        //$referer = $request->getSession()->get('categories_referer');
        //$request->getSession()->remove('categories_referer');
        $referer = SessionUtil::getVar('categories_referer');
        SessionUtil::DelVar('categories_referer');

        return new RedirectResponse(System::normalizeUrl($referer));
    }

    /**
     * @Route("/usercategories")
     *
     * return the categories for the currently logged in user, really only used for testing purposes
     *
     * @return array array of categories
     */
    public function getusercategoriesAction()
    {
        return ModUtil::apiFunc('ZikulaCategoriesModule', 'user', 'getusercategories');
    }

    /**
     * @Route("/usercategoryname")
     *
     * return the category name for a user, really only used for testing purposes
     *
     * @return string the username associated with the category
     */
    public function getusercategorynameAction()
    {
        return ModUtil::apiFunc('ZikulaCategoriesModule', 'user', 'getusercategoryname');
    }
}

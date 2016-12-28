<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use System;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\Core\Controller\AbstractController;
use ZLanguage;

/**
 * User controllers for the categories module.
 */
class UserController extends AbstractController
{
    /**
     * @Route("")
     *
     * Main user function.
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the module
     */
    public function indexAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $referer = $request->server->get('HTTP_REFERER');
        if (strpos($referer, '/categories') === false) {
            $request->getSession()->set('categories_referer', $referer);
        }

        $allowed = $this->getVar('allowusercatedit', 0);
        if ($allowed) {
            return $this->redirectToRoute('zikulacategoriesmodule_user_edituser');
        }

        $this->addFlash('error', $this->__('Sorry! User-owned category editing has not been enabled. This feature can be enabled by the site administrator.'));

        return $this->responseForErrorMessage();
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
        @trigger_error('The zikulcategoriesmodule_user_main action is deprecated. please use the index action instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulacategoriesmodule_user_index');
    }

    /**
     * @Route("/edit")
     * @Template
     *
     * Edit category for a simple, non-recursive set of categories.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the document root
     */
    public function editAction(Request $request)
    {
        $docroot = $request->query->get('dr', 0);
        $cid = $request->query->get('cid', 0);

        if (!$this->hasPermission('ZikulaCategoriesModule::category', "ID::$docroot", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $referer = $request->headers->get('referer');
        if (strpos($referer, 'categories') === false) {
            $request->getSession()->set('categories_referer', $referer);
        }

        $editCat = [];

        if (!$docroot) {
            $this->addFlash('error', $this->__("Error! The URL contains an invalid 'document root' parameter."));

            return $this->responseForErrorMessage();
        }
        if ($docroot == 1) {
            $this->addFlash('error', $this->__("Error! The root directory cannot be modified in 'user' mode"));

            return $this->responseForErrorMessage();
        }

        $categoryApi = $this->get('zikula_categories_module.api.category');

        if (is_int((int)$docroot) && $docroot > 0) {
            $rootCat = $categoryApi->getCategoryById($docroot);
        } else {
            $rootCat = $categoryApi->getCategoryByPath($docroot);
            if (!$rootCat) {
                $rootCat = $categoryApi->getCategoryByPath($docroot, 'ipath');
            }
        }

        // now check if someone is trying edit another user's categories
        $userRoot = $this->getVar('userrootcat', 0);
        if ($userRoot) {
            $userRootCat = $categoryApi->getCategoryByPath($userRoot);
            if ($userRootCat) {
                $userRootCatIPath = $userRootCat['ipath'];
                $rootCatIPath = $rootCat['ipath'];
                if (strpos($rootCatIPath, $userRootCatIPath) !== false) {
                    if (!$this->hasPermission('Categories::category', "ID::$docroot", ACCESS_ADMIN)) {
                        $userRootCatPath = $userRootCat['path'];
                        $rootCatPath = $rootCat['path'];
                        if (strpos($rootCatPath, $userRootCatPath) === false) {
                            //! %s represents the root path (id), passed in the url
                            $this->addFlash('error', $this->__f("Error! It looks like you are trying to edit another user's categories. Only site administrators can do that (%s).", ['%s' => $docroot]));

                            return $this->responseForErrorMessage();
                        }
                    }
                }
            }
        }

        if ($cid) {
            $editCat = $categoryApi->getCategoryById($cid);
            if ($editCat['is_locked']) {
                $this->addFlash('error', $this->__f('Notice: The administrator has locked the category \'%category\' (ID \'%id\'). You cannot edit or delete it.', ['%category' => $editCat['name'], '%id' => $cid]));

                return $this->responseForErrorMessage();
            }
        }

        if (!$rootCat) {
            $this->addFlash('error', $this->__f('Error! Cannot access root directory (%s).', ['%s' => $docroot]));

            return $this->responseForErrorMessage();
        }
        if ($editCat && !$editCat['is_leaf']) {
            $this->addFlash('error', $this->__f('Error! The specified category is not a leaf-level category (%s).', ['%s' => $cid]));

            return $this->responseForErrorMessage();
        }
        if ($editCat && !$this->get('zikula_categories_module.hierarchy_helper')->isDirectSubCategory($rootCat, $editCat)) {
            $this->addFlash('error', $this->__f('Error! The specified category is not a child of the document root (%docroot; %id).', ['%docroot' => $docroot, '%id' => $cid]));

            return $this->responseForErrorMessage();
        }

        $allCats = $categoryApi->getSubCategoriesForCategory($rootCat, false, false, false, true, true);

        $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : [];

        return [
            'rootCat' => $rootCat,
            'category' => $editCat,
            'attributes' => $attributes,
            'allCats' => $allCats,
            'languages' => ZLanguage::getInstalledLanguages(),
            'userlanguage' => $request->getLocale(),
            'referer' => $request->getSession()->get('categories_referer'),
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
        ];
    }

    /**
     * @Route("/edituser")
     *
     * Edit categories for the currently logged in user.
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
        if (!$this->hasPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException($this->__('Error! Editing mode for user-owned categories is only available to users who have logged-in.'));
        }

        $allowUserEdit = $this->getVar('allowusercatedit', 0);
        if (!$allowUserEdit) {
            $this->addFlash('error', $this->__('Error! User-owned category editing has not been enabled. This feature can be enabled by the site administrator.'));

            return $this->responseForErrorMessage();
        }

        $userRoot = $this->getVar('userrootcat', 0);
        if (!$userRoot) {
            $this->addFlash('error', $this->__('Error! Could not determine the user root node.'));

            return $this->responseForErrorMessage();
        }

        $categoryApi = $this->get('zikula_categories_module.api.category');

        $userRootCat = $categoryApi->getCategoryByPath($userRoot);
        if (!$userRoot) {
            $this->addFlash('error', $this->__f('Error! The user root node seems to point towards an invalid category: %s.', ['%s' => $userRoot]));

            return $this->responseForErrorMessage();
        }

        if ($userRootCat == 1) {
            $this->addFlash('error', $this->__("Error! The root directory cannot be modified in 'user' mode"));

            return $this->responseForErrorMessage();
        }

        $userCatName = $this->get('zikula_categories_module.api.user_categories')->getUserCategoryName();
        if (!$userCatName) {
            $this->addFlash('error', $this->__('Error! Cannot determine user category root node name.'));

            return $this->responseForErrorMessage();
        }

        $thisUserRootCatPath = $userRoot . '/' . $userCatName;
        $thisUserRootCat = $categoryApi->getCategoryByPath($thisUserRootCatPath);

        $dr = null;
        if (!$thisUserRootCat) {
            $autoCreate = $this->getVar('autocreateusercat', 0);
            if (!$autoCreate) {
                $this->addFlash('error', $this->__('Error! The user root category node for this user does not exist, and the automatic creation flag (autocreate) has not been set.'));

                return $this->responseForErrorMessage();
            }

            $entityManager = $this->get('doctrine')->getManager();

            $cat = [
                'id' => '',
                'parent' => $entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $userRootCat['id']),
                'name' => $userCatName,
                'display_name' => [$request->getLocale() => $userCatName],
                'display_desc' => [$request->getLocale() => ''],
                'path' => $thisUserRootCatPath,
                'status' => 'A'
            ];

            $obj = new CategoryEntity();
            $obj->merge($cat);
            $entityManager->persist($obj);
            $entityManager->flush();

            // since the original insert can't construct the ipath (as
            // the insert id is not known yet) we update the object here
            $obj->setIPath($userRootCat['ipath'] . '/' . $obj['id']);
            $entityManager->flush();

            $dr = $obj->getID();

            $autoCreateDefaultUserCat = $this->getVar('autocreateuserdefaultcat', 0);
            if ($autoCreateDefaultUserCat) {
                $userdefaultcatname = $this->getVar('userdefaultcatname', $this->__('Default'));
                $cat = [
                    'id' => '',
                    'parent' => $entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $dr),
                    'is_leaf' => 1,
                    'name' => $userdefaultcatname,
                    'sort_value' => 0,
                    'display_name' => [$request->getLocale() => $userdefaultcatname],
                    'display_desc' => [$request->getLocale() => ''],
                    'path' => $thisUserRootCatPath . '/' . $userdefaultcatname,
                    'status' => 'A'
                ];

                $obj2 = new CategoryEntity();
                $obj2->merge($cat);
                $entityManager->persist($obj2);
                $entityManager->flush();

                // since the original insert can't construct the ipath (as
                // the insert id is not known yet) we update the object here
                $obj2->setIPath($obj['ipath'] . '/' . $obj2['id']);
                $entityManager->flush();
            }
        } else {
            $dr = $thisUserRootCat['id'];
        }

        return $this->redirectToRoute('zikulacategoriesmodule_user_edit', ['dr' => $dr]);
    }

    /**
     * @Route("/refer")
     *
     * Refers the user back to the calling page.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function referBackAction(Request $request)
    {
        $referer = $request->getSession()->get('categories_referer');
        $request->getSession()->remove('categories_referer');

        return $this->redirect(System::normalizeUrl($referer));
    }

    /**
     * @Route("/usercategories")
     *
     * Returns the categories for the currently logged in user, really only used for testing purposes.
     *
     * @return array array of categories
     */
    public function getusercategoriesAction()
    {
        return $this->get('zikula_categories_module.api.user_categories')->getUserCategories();
    }

    /**
     * @Route("/delete")
     *
     * Deletes a category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have delete permissions over the module
     * @throws \InvalidArgumentException Thrown if the category or document root aren't supplied or are invalid
     */
    public function deleteAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $cid = $request->query->getInt('cid', 0);
        $dr = $request->query->getInt('dr', 0);
        $url = $request->server->get('HTTP_REFERER');

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        if (!$cid) {
            throw new \InvalidArgumentException($this->__('Error! The category ID is invalid.'));
        }

        $categoryApi = $this->get('zikula_categories_module.api.category');

        $category = $categoryApi->getCategoryById($cid);

        if (!$category) {
            throw new \InvalidArgumentException($this->__f('Error! Cannot retrieve category with ID %s.', ['%s' => $cid]));
        }

        if ($category['is_locked']) {
            //! %1$s is the id, %2$s is the name
            $this->addFlash('error', $this->__f('Notice: The administrator has locked the category \'%category\' (ID \'%id\'). You cannot edit or delete it.', ['%category' => $category['name'], '%id' => $cid]));

            return new RedirectResponse(System::normalizeUrl($url));
        }

        $categoryApi->deleteCategoryById($cid);

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/update")
     * @Method("POST")
     *
     * Updates a category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     * @throws \InvalidArgumentException Thrown if the document root is invalid or
     *                                          if the category id doesn't match a valid category
     */
    public function updateAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $dr = $request->request->request->getInt('dr', 0);
        $ref = $request->server->get('HTTP_REFERER');

        $returnfunc = false !== strpos($ref, 'edituser') ? 'edituser' : 'edit';
        $url = $this->get('router')->generate('zikulacategoriesmodule_user_' . $returnfunc, ['dr' => $dr], RouterInterface::ABSOLUTE_URL);

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        // get data from post
        $data = $request->request->get('category', null);
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        $valid = $processingHelper->validateCategoryData($data);
        if (!$valid) {
            return new RedirectResponse(System::normalizeUrl($url));
        }

        // process name
        $data['name'] = $processingHelper->processCategoryName($data['name']);

        // process parent
        $data['parent'] = $processingHelper->processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = $processingHelper->processCategoryDisplayName($data['display_name'], $data['name']);

        // get existing category
        $entityManager = $this->get('doctrine')->getManager();
        $category = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $data['id']);

        if (!$category) {
            throw new \InvalidArgumentException($this->__f('Error! Cannot retrieve category with ID %s.', ['%s' => $data['id']]));
        }

        if ($category['is_locked']) {
            $this->addFlash('error', $this->__f('Notice: The administrator has locked the category \'%category\' (ID \'%id\'). You cannot edit or delete it.', ['%category' => $category['name'], '%id' => $data['id']]));

            return new RedirectResponse(System::normalizeUrl($url));
        }

        $category_old_name = $category['name'];

        // save category
        $category->merge($data);
        $entityManager->persist($category);
        $entityManager->flush();

        // process path and ipath
        $category['path'] = $processingHelper->processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = $processingHelper->processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $request->request->get('attribute_name', []);
        $attrib_values = $request->request->get('attribute_value', []);
        $processingHelper->processCategoryAttributes($category, $attrib_names, $attrib_values);

        $entityManager->flush();

        if ($category_old_name != $category['name']) {
            $this->get('zikula_categories_module.path_builder_helper')->rebuildPaths('path', 'name', $category['id']);
        }

        $this->addFlash('status', $this->__f('Done! Saved the %s category.', ['%s' => $category_old_name]));

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/move/{cid}/{dr}/{direction}", requirements={"cid" = "^[1-9]\d*$", "dr" = "^[1-9]\d*$", "direction" = "up|down"})
     * @Method("GET")
     *
     * Moves a field.
     *
     * @param Request $request
     * @param integer $cid
     * @param integer $dr
     * @param string $direction
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     */
    public function moveFieldAction(Request $request, $cid, $dr, $direction = null)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $url = $request->server->get('HTTP_REFERER');

        $cats1 = $this->get('zikula_categories_module.api.category')->getSubCategories($dr, false, false, false, false);
        $cats2 = $this->get('zikula_categories_module.category_sorting_helper')->resequence($cats1, 10);

        $sortValues = [];

        $entityManager = $this->get('doctrine')->getManager();

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
            $sortValues[] = [
                'id' => $obj['id'],
                'sort_value' => $obj['sort_value']
            ];
        }

        $entityManager->flush();

        $obj = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cid);

        $amountOfSortValues = count($sortValues);
        for ($i = 0; $i < $amountOfSortValues; $i++) {
            if ($sortValues[$i]['id'] == $cid) {
                if ($direction == 'up') {
                    if ($sortValues[$i - 1]['sort_value']) {
                        $obj['sort_value'] = $sortValues[$i - 1]['sort_value'] - 1;
                    }
                } else {
                    if ($sortValues[$i + 1]['sort_value']) {
                        $obj['sort_value'] = $sortValues[$i + 1]['sort_value'] + 1;
                    }
                }
            }
        }

        $entityManager->flush();

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/new")
     * @Method("POST")
     *
     * Creates a new category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have add permissions to the module
     * @throws \InvalidArgumentException Thrown if the document root is invalid
     */
    public function newcatAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $dr = $request->request->getInt('dr', 0);
        $url = $request->server->get('HTTP_REFERER');

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        // get data from post
        $data = $request->request->get('category', null);
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        $valid = $processingHelper->validateCategoryData($data);
        if (!$valid) {
            return $this->redirectToRoute('zikulacategoriesmodule_user_edit', ['dr' => $dr]);
        }

        // process name
        $data['name'] = $processingHelper->processCategoryName($data['name']);

        // process parent
        $data['parent'] = $processingHelper->processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = $processingHelper->processCategoryDisplayName($data['display_name'], $data['name']);

        // process sort value
        $data['sort_value'] = 0;

        // save category
        $entityManager = $this->get('doctrine')->getManager();
        $category = new CategoryEntity();
        $category->merge($data);
        $entityManager->persist($category);
        $entityManager->flush();

        // process path and ipath
        $category['path'] = $processingHelper->processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = $processingHelper->processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $request->request->get('attribute_name', []);
        $attrib_values = $request->request->get('attribute_value', []);
        $processingHelper->processCategoryAttributes($category, $attrib_names, $attrib_values);

        $entityManager->flush();

        $this->addFlash('status', $this->__f('Done! Inserted the %s category.', ['%s' => $data['name']]));

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/resequence/{dr}", requirements={"dr" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Resequences categories.
     *
     * @param Request $request
     * @param integer $dr
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     */
    public function resequenceAction(Request $request, $dr)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $url = $request->server->get('HTTP_REFERER');

        $cats1 = $this->get('zikula_categories_module.api.category')->getSubCategories($dr, false, false, false, false);
        $cats2 = $this->get('zikula_categories_module.category_sorting_helper')->resequence($cats1, 10);

        $entityManager = $this->get('doctrine')->getManager();

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
        }

        $entityManager->flush();

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/usercategoryname")
     *
     * Returns the category name for a user, really only used for testing purposes.
     *
     * @return string the username associated with the category
     */
    public function getusercategorynameAction()
    {
        return $this->get('zikula_categories_module.api.user_categories')->getUserCategoryName();
    }

    private function responseForErrorMessage()
    {
        return $this->render('@ZikulaCategoriesModule/User/editcategories.html.twig');
    }
}

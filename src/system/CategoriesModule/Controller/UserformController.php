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

use CategoryUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use System;
use Zikula\CategoriesModule\GenericUtil;
use Zikula\Core\Controller\AbstractController;

/**
 * User form controllers for the categories module.
 */
class UserformController extends AbstractController
{
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

        $cid = (int)$request->query->get('cid', 0);
        $dr = (int)$request->query->get('dr', 0);
        $url = $request->server->get('HTTP_REFERER');

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        if (!$cid) {
            throw new \InvalidArgumentException($this->__('Error! The category ID is invalid.'));
        }

        $category = CategoryUtil::getCategoryByID($cid);

        if (!$category) {
            throw new \InvalidArgumentException($this->__f('Error! Cannot retrieve category with ID %s.', ['%s' => $cid]));
        }

        if ($category['is_locked']) {
            //! %1$s is the id, %2$s is the name
            $this->addFlash('error', $this->__f('Notice: The administrator has locked the category \'%category\' (ID \'%id\'). You cannot edit or delete it.', ['%category' => $category['name'], '%id' => $cid]));

            return new RedirectResponse(System::normalizeUrl($url));
        }

        CategoryUtil::deleteCategoryByID($cid);

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
    public function editAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $dr = (int)$request->request->request->get('dr', 0);
        $ref = $request->server->get('HTTP_REFERER');

        $returnfunc = false !== strpos($ref, 'useredit') ? 'useredit' : 'edit';
        $url = $this->get('router')->generate('zikulacategoriesmodule_user_' . $returnfunc, ['dr' => $dr], RouterInterface::ABSOLUTE_URL);

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        // get data from post
        $data = $request->request->get('category', null);

        $valid = GenericUtil::validateCategoryData($data);
        if (!$valid) {
            return new RedirectResponse(System::normalizeUrl($url));
        }

        // process name
        $data['name'] = GenericUtil::processCategoryName($data['name']);

        // process parent
        $data['parent'] = GenericUtil::processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = GenericUtil::processCategoryDisplayName($data['display_name'], $data['name']);

        // get existing category
        $entityManager = $this->get('doctrine.orm.entity_manager');
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
        $category['path'] = GenericUtil::processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = GenericUtil::processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $request->request->get('attribute_name', []);
        $attrib_values = $request->request->get('attribute_value', []);
        GenericUtil::processCategoryAttributes($category, $attrib_names, $attrib_values);

        $entityManager->flush();

        if ($category_old_name != $category['name']) {
            CategoryUtil::rebuildPaths('path', 'name', $category['id']);
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

        $cats1 = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats2 = CategoryUtil::resequence($cats1, 10);

        $sort_values = [];

        $entityManager = $this->get('doctrine.orm.entity_manager');

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
            $sort_values[] = [
                'id' => $obj['id'],
                'sort_value' => $obj['sort_value']
            ];
        }

        $entityManager->flush();

        $obj = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cid);

        for ($i = 0; $i < count($sort_values); $i++) {
            if ($sort_values[$i]['id'] == $cid) {
                if ($direction == 'up') {
                    if ($sort_values[$i - 1]['sort_value']) {
                        $obj['sort_value'] = $sort_values[$i - 1]['sort_value'] - 1;
                    }
                } else {
                    if ($sort_values[$i + 1]['sort_value']) {
                        $obj['sort_value'] = $sort_values[$i + 1]['sort_value'] + 1;
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

        $dr = (int)$request->request->get('dr', 0);
        $url = $request->server->get('HTTP_REFERER');

        if (!$dr) {
            throw new \InvalidArgumentException($this->__('Error! The document root is invalid.'));
        }

        // get data from post
        $data = $request->request->get('category', null);

        $valid = GenericUtil::validateCategoryData($data);
        if (!$valid) {
            return $this->redirectToRoute('zikulacategoriesmodule_user_edit', ['dr' => $dr]);
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
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $category = new \Zikula\CategoriesModule\Entity\CategoryEntity();
        $category->merge($data);
        $entityManager->persist($category);
        $entityManager->flush();

        // process path and ipath
        $category['path'] = GenericUtil::processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = GenericUtil::processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $request->request->get('attribute_name', []);
        $attrib_values = $request->request->get('attribute_value', []);
        GenericUtil::processCategoryAttributes($category, $attrib_names, $attrib_values);

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

        $cats1 = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats2 = CategoryUtil::resequence($cats1, 10);

        $entityManager = $this->get('doctrine.orm.entity_manager');

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
        }

        $entityManager->flush();

        return new RedirectResponse(System::normalizeUrl($url));
    }
}

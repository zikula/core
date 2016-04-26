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
use CategoryUtil;
use Zikula\CategoriesModule\GenericUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;

/**
 * User form controllers for the categories module
 */
class UserformController extends \Zikula_AbstractController
{
    /**
     * @Route("/delete")
     *
     * delete category
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
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $cid = (int)$request->get('cid', 0);
        $dr = (int)$request->get('dr', 0);
        $url = $request->server->get('HTTP_REFERER');

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
            $request->getSession()->getFlashBag()->add('error', $this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', [$cid, $category['name']]), null, $url);

            return new RedirectResponse(System::normalizeUrl($url));
        }

        CategoryUtil::deleteCategoryByID($cid);

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/update")
     * @Method("POST")
     *
     * update category
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
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $dr = (int)$request->request->get('dr', 0);
        $ref = $request->server->get('HTTP_REFERER');

        $returnfunc = strpos($ref, "useredit") !== false ? 'useredit' : 'edit';
        $url = $this->get('router')->generate("zikulacategoriesmodule_user_$returnfunc", ['dr' => $dr], RouterInterface::ABSOLUTE_URL);

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
        $category = $this->entityManager->find('ZikulaCategoriesModule:CategoryEntity', $data['id']);

        if (!$category) {
            throw new \InvalidArgumentException($this->__f('Error! Cannot retrieve category with ID %s.', $data['id']));
        }

        if ($category['is_locked']) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Notice: The administrator has locked the category \'%2$s\' (ID \'%$1s\'). You cannot edit or delete it.', [$data['id'], $category['name']]));

            return new RedirectResponse(System::normalizeUrl($url));
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
        $attrib_names = $request->request->get('attribute_name', []);
        $attrib_values = $request->request->get('attribute_value', []);
        GenericUtil::processCategoryAttributes($category, $attrib_names, $attrib_values);

        $this->entityManager->flush();

        if ($category_old_name != $category['name']) {
            CategoryUtil::rebuildPaths('path', 'name', $category['id']);
        }

        $msg = $this->__f('Done! Saved the %s category.', $category_old_name);
        $request->getSession()->getFlashBag()->add('status', $msg);

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/move/{cid}/{dr}/{direction}", requirements={"cid" = "^[1-9]\d*$", "dr" = "^[1-9]\d*$", "direction" = "up|down"})
     * @Method("GET")
     *
     * move field
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
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $url = $request->server->get('HTTP_REFERER');

        $cats1 = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats2 = CategoryUtil::resequence($cats1, 10);

        $sort_values = [];

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $this->entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
            $sort_values[] = [
                'id' => $obj['id'],
                'sort_value' => $obj['sort_value']
            ];
        }

        $this->entityManager->flush();

        $obj = $this->entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cid);

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

        $this->entityManager->flush();

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/new")
     * @Method("POST")
     *
     * create category
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
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
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
            return new RedirectResponse($this->get('router')->generate('zikulacategoriesmodule_user_edit', ['dr' => $dr], RouterInterface::ABSOLUTE_URL));
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
        $category = new \Zikula\CategoriesModule\Entity\CategoryEntity();
        $category->merge($data);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // process path and ipath
        $category['path'] = GenericUtil::processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = GenericUtil::processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $request->request->get('attribute_name', []);
        $attrib_values = $request->request->get('attribute_value', []);
        GenericUtil::processCategoryAttributes($category, $attrib_names, $attrib_values);

        $this->entityManager->flush();

        $msg = $this->__f('Done! Inserted the %s category.', $data['name']);
        $request->getSession()->getFlashBag()->add('status', $msg);

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/resequence/{dr}", requirements={"dr" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * resequence categories
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
        if (!SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $url = $request->server->get('HTTP_REFERER');

        $cats1 = CategoryUtil::getSubCategories($dr, false, false, false, false);
        $cats2 = CategoryUtil::resequence($cats1, 10);

        $ak = array_keys($cats1);
        foreach ($ak as $k) {
            $obj = $this->entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cats1[$k]['id']);
            $obj['sort_value'] = $cats2[$k]['sort_value'];
        }

        $this->entityManager->flush();

        return new RedirectResponse(System::normalizeUrl($url));
    }
}

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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\Core\Controller\AbstractController;

/**
 * @Route("/admin")
 *
 * Admin form controllers for the categories module.
 */
class AdminformController extends AbstractController
{
    /**
     * @Route("/edit")
     * @Method("POST")
     *
     * Updates a category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function editAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get data from post
        $data = $request->request->get('category', null);

        if (!isset($data['is_locked'])) {
            $data['is_locked'] = 0;
        }
        if (!isset($data['is_leaf'])) {
            $data['is_leaf'] = 0;
        }
        if (!isset($data['status'])) {
            $data['status'] = 'I';
        }

        $args = [];

        if ($request->request->get('category_copy', null)) {
            $args['op'] = 'copy';
            $args['cid'] = (int)$data['id'];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_op', $args);
        }

        if ($request->request->get('category_move', null)) {
            $args['op'] = 'move';
            $args['cid'] = (int)$data['id'];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_op', $args);
        }

        if ($request->request->get('category_delete', null)) {
            $args['op'] = 'delete';
            $args['cid'] = (int)$data['id'];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_op', $args);
        }

        if ($request->request->get('category_user_edit', null)) {
            $_SESSION['category_referer'] = $request->server->get('HTTP_REFERER');
            $args['dr'] = (int)$data['id'];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_edit', $args);
        }

        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        $valid = $processingHelper->validateCategoryData($data);
        if (!$valid) {
            $args = [
                'mode' => 'edit',
                'cid' => (int)$data['id']
            ];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_edit', $args);
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

        $prevCategoryName = $category['name'];

        // save category
        $category->merge($data);
        $entityManager->flush();

        // process path and ipath
        $category['path'] = $processingHelper->processCategoryPath($data['parent']['path'], $category['name']);
        $category['ipath'] = $processingHelper->processCategoryIPath($data['parent']['ipath'], $category['id']);

        // process category attributes
        $attrib_names = $request->request->get('attribute_name', []);
        $attrib_values = $request->request->get('attribute_value', []);
        $processingHelper->processCategoryAttributes($category, $attrib_names, $attrib_values);

        $entityManager->flush();

        // since a name change will change the object path, we must rebuild it here
        if ($prevCategoryName != $category['name']) {
            $this->get('zikula_categories_module.path_builder_helper')->rebuildPaths('path', 'name', $category['id']);
        }

        $this->addFlash('status', $this->__f('Done! Saved the %s category.', ['%s' => $prevCategoryName]));

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("/new")
     * @Method("POST")
     *
     * Creates a category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to add a category
     */
    public function newcatAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        // get data from post
        $data = $request->request->get('category', null);
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        $valid = $processingHelper->validateCategoryData($data);
        if (!$valid) {
            return $this->redirectToRoute('zikulacategoriesmodule_admin_newcat');
        }

        // process name
        $data['name'] = $processingHelper->processCategoryName($data['name']);

        // process parent
        $data['parent'] = $processingHelper->processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = $processingHelper->processCategoryDisplayName($data['display_name'], $data['name']);

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

        $this->addFlash('status', $this->__f('Done! Inserted the %s category.', ['%s' => $category['name']]));

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("/delete")
     * @Method("POST")
     *
     * Deletes a category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to delete a category
     */
    public function deleteAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        if ($request->request->get('category_cancel', null)) {
            return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
        }

        $categoryApi = $this->get('zikula_categories_module.api.category');
        $cid = $request->request->get('cid', null);
        $cat = $categoryApi->getCategoryById($cid);
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        // prevent deletion if category is already used
        if (!$processingHelper->mayCategoryBeDeletedOrMoved($cat)) {
            $this->addFlash('error', $this->__f('Error! Category %s can not be deleted, because it is already used.', ['%s' => $cat['name']]));

            return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
        }

        // delete subdirectories
        if ($request->request->get('subcat_action') == 'delete') {
            $categoryApi->deleteCategoriesByPath($cat['ipath']);
        } elseif ($request->request->get('subcat_action') == 'move') {
            // move subdirectories
            $data = $request->request->get('category', null);
            if ($data['parent_id']) {
                $this->get('zikula_categories_module.copy_and_move_helper')->moveSubCategoriesByPath($cat['ipath'], $data['parent_id']);
                $categoryApi->deleteCategoryById($cid);
            }
        }

        $this->addFlash('status', $this->__f('Done! Deleted the %s category.', ['%s' => $cat['name']]));

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("/copy")
     * @Method("POST")
     *
     * Copies a category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to add a category
     */
    public function copyAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        if ($request->request->get('category_cancel', null)) {
            return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
        }

        $cid = $request->request->get('cid', null);
        $cat = $this->get('zikula_categories_module.api.category')->getCategoryByID($cid);

        $data = $request->request->get('category', null);

        $this->get('zikula_categories_module.copy_and_move_helper')->copyCategoriesByPath($cat['ipath'], $data['parent_id']);

        $this->addFlash('status', $this->__f('Done! Copied the %s category.', ['%s' => $cat['name']]));

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("/move")
     * @Method("POST")
     *
     * Moves a category.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to edit a category
     */
    public function moveAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if ($request->request->get('category_cancel', null)) {
            return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
        }

        $cid = $request->request->get('cid', null);
        $cat = $this->get('zikula_categories_module.api.category')->getCategoryById($cid);
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        // prevent move if category is already used
        if (!$processingHelper->mayCategoryBeDeletedOrMoved($cat)) {
            $this->addFlash('error', $this->__f('Error! Category %s can not be moved, because it is already used.', ['%s' => $cat['name']]));

            return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
        }

        $data = $request->request->get('category', null);

        $this->get('zikula_categories_module.copy_and_move_helper')->moveCategoriesByPath($cat['ipath'], $data['parent_id']);

        $this->addFlash('status', $this->__f('Done! Moved the %s category.', ['%s' => $cat['name']]));

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("/rebuild")
     *
     * Rebuilds path structure.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function rebuildPathsAction()
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $pathBuilder = $this->get('zikula_categories_module.path_builder_helper');
        $pathBuilder->rebuildPaths('path', 'name');
        $pathBuilder->rebuildPaths('ipath', 'id');

        $this->addFlash('status', $this->__('Done! Rebuilt the category paths.'));

        return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
    }

    /**
     * @Route("/editregistry")
     * @Method("POST")
     *
     * Creates, updates or deletes a category registry.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function editregistryAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $entityManager = $this->get('doctrine')->getManager();

        // delete registry
        if ($request->request->get('mode', null) == 'delete') {
            $id = $request->get('id', 0);
            $obj = $entityManager->find('ZikulaCategoriesModule:CategoryRegistryEntity', $id);
            $entityManager->remove($obj);
            $entityManager->flush();

            $this->addFlash('status', $this->__('Done! Deleted the category registry entry.'));

            return $this->redirectToRoute('zikulacategoriesmodule_admin_editregistry');
        }

        if (!$request->request->get('category_submit', null)) {
            // got here through selector auto-submit
            $routeArgs = [
                'category_registry' => $request->request->get('category_registry', null)
            ];

            return $this->redirectToRoute('zikulacategoriesmodule_admin_editregistry', $routeArgs);
        }

        // get data from post
        $data = $request->request->get('category_registry', null);

        // do some validation
        $valid = true;
        if (empty($data['modname'])) {
            $this->addFlash('error', $this->__('Error! You did not select a module.'));
            $valid = false;
        }
        if (empty($data['entityname'])) {
            $this->addFlash('error', $this->__('Error! You did not select an entity.'));
            $valid = false;
        }
        if (empty($data['property'])) {
            $this->addFlash('error', $this->__('Error! You did not enter a property name.'));
            $valid = false;
        }
        if ((int)$data['category_id'] == 0) {
            $this->addFlash('error', $this->__('Error! You did not select a category.'));
            $valid = false;
        }
        if (!$valid) {
            return $this->redirectToRoute('zikulacategoriesmodule_admin_editregistry');
        }

        if (isset($data['id']) && (int)$data['id'] > 0) {
            // update existing registry
            $obj = $entityManager->find('ZikulaCategoriesModule:CategoryRegistryEntity', $data['id']);
        } else {
            // create new registry
            $obj = new CategoryRegistryEntity();
        }
        $obj->merge($data);
        $entityManager->persist($obj);
        $entityManager->flush();
        $this->addFlash('status', $this->__('Done! Saved the category registry entry.'));

        return $this->redirectToRoute('zikulacategoriesmodule_admin_editregistry');
    }
}

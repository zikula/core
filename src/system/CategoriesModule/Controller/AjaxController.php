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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\BadDataResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
use Zikula\Core\Response\Ajax\NotFoundResponse;
use ZLanguage;

/**
 * @Route("/ajax")
 *
 * Ajax controllers for the categories module.
 */
class AjaxController extends AbstractController
{
    /**
     * @Route("/resequence", options={"expose"=true})
     * @Method("POST")
     *
     * Resequence categories
     *
     * @param Request $request
     *
     * @return AjaxResponse|ForbiddenResponse
     */
    public function resequenceAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }

        $tree = $request->request->get('tree');

        $entityManager = $this->get('doctrine')->getManager();
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        foreach ($tree as $catData) {
            if (empty($catData)) {
                continue;
            }
            /** @var CategoryEntity $category */
            $category = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $catData['id']);
            $category->setSort_value($catData['lineno']);
            if (!empty($catData['parent'])) {
                /** @var CategoryEntity $parent */
                $parent = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $catData['parent']);
                $category->setParent($parent);
                // reset paths
                $category->setPath($processingHelper->processCategoryPath($parent->getPath(), $category->getName()));
                $category->setIPath($processingHelper->processCategoryIPath($parent->getIPath(), $category->getId()));
            } else {
                $category->setParent(null);
            }
        }

        $entityManager->flush();

        $result = [
            'response' => true
        ];

        return new AjaxResponse($result);
    }

    /**
     * @Route("/edit", options={"expose"=true})
     * @Method("POST")
     *
     * Edit a category
     *
     *      string $mode   the mode of operation (new or edit)
     *      int    $cid    the category id
     *      int    $parent the parent category id
     *                       }
     *
     * @param Request $request
     *
     * @return AjaxResponse|NotFoundResponse ajax response object
     */
    public function editAction(Request $request)
    {
        $mode = $request->request->get('mode', 'new');
        $accessLevel = $mode == 'edit' ? ACCESS_EDIT : ACCESS_ADD;
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', $accessLevel)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }

        $cid = $request->request->get('cid', 0);
        $parent = $request->request->get('parent', 1);
        $validationErrors = [];
        $validationErrorsInSession = $request->getSession()->get('validationErrors', '');
        if (is_array($validationErrorsInSession)) {
            $validationErrors = $validationErrorsInSession;
            $request->getSession()->remove('validationErrors');
        }

        $editCat = '';

        // indicates that we're editing
        if ($mode == 'edit') {
            if (!$cid) {
                return new BadDataResponse($this->__('Error! Cannot determine valid \'cid\' for edit mode in \'Categories_admin_edit\'.'));
            }
            $editCat = $this->get('zikula_categories_module.api.category')->getCategoryById($cid);
            if (!$editCat) {
                return new NotFoundResponse($this->__('Sorry! No such item found.'));
            }
        } else {
            // someone just pressed 'new' -> populate defaults
            $editCat['sort_value'] = '0';
            $editCat['parent_id'] = $parent;
        }

        $attributes = isset($editCat['__ATTRIBUTES__']) ? $editCat['__ATTRIBUTES__'] : [];

        $templateParameters = [
            'mode' => $mode,
            'category' => $editCat,
            'attributes' => $attributes,
            'languages' => ZLanguage::getInstalledLanguages()
        ];

        $result = [
            'action' => $mode == 'new' ? 'add' : 'edit',
            'result' => $this->renderView('@ZikulaCategoriesModule/Ajax/edit.html.twig', $templateParameters),
            'validationErrors' => $validationErrors
        ];

        if ($validationErrors) {
            return new BadDataResponse($validationErrors, $result);
        }

        return new AjaxResponse($result);
    }

    /**
     * @Route("/copy", options={"expose"=true})
     * @Method("POST")
     *
     * Copy a category
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     */
    public function copyAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }

        $cid = $request->request->get('cid');
        $parent = $request->request->get('parent');
        $categoryApi = $this->get('zikula_categories_module.api.category');

        $cat = $categoryApi->getCategoryById($cid);
        $this->get('zikula_categories_module.copy_and_move_helper')->copyCategoriesByPath($cat['ipath'], $parent);

        $copyParent = $categoryApi->getCategoryById($cat['parent_id']);

        $categories = $categoryApi->getSubCategories($copyParent['id'], true, true, true, true, true);

        $entityManager = $this->get('doctrine')->getManager();

        // get the last added category in the parent
        $category = $entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity')->getLastByParent($parent);

        // create jsTree node
        $node = $this->get('zikula_categories_module.js_tree_helper')->getJsTreeNodeFromCategory($category);

        $leafStatus = [
            'leaf' => [],
            'noleaf' => []
        ];
        foreach ($categories as $c) {
            if ($c['is_leaf']) {
                $leafStatus['leaf'][] = $c['id'];
            } else {
                $leafStatus['noleaf'][] = $c['id'];
            }
        }
        $result = [
            'action' => 'copy',
            'cid' => $cid,
            'copycid' => $copyParent['id'],
            'parent' => $category->getParent()->getId(),
            'node' => $node,
            'leafstatus' => $leafStatus,
            'result' => true
        ];

        return new AjaxResponse($result);
    }

    /**
     * @Route("/delete", options={"expose"=true})
     * @Method("POST")
     *
     * Delete a category
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     */
    public function deleteAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_DELETE)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }

        $cid = $request->request->get('cid');
        $categoryApi = $this->get('zikula_categories_module.api.category');
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        $cat = $categoryApi->getCategoryById($cid);

        // prevent deletion if category is already used
        if (!$processingHelper->mayCategoryBeDeletedOrMoved($cat)) {
            return new BadDataResponse($this->__f('Error! Category %s can not be deleted, because it is already used.', ['%s' => $cat['name']]));
        }

        $categoryApi->deleteCategoriesByPath($cat['ipath']);

        $result = [
            'action' => 'delete',
            'cid' => $cid,
            'result' => true
        ];

        return new AjaxResponse($result);
    }

    /**
     * @Route("/deleteandmove", options={"expose"=true})
     * @Method("POST")
     *
     * Delete a category and move any existing subcategories
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     */
    public function deleteandmovesubsAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_DELETE)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }

        $cid = $request->request->get('cid');
        $parent = $request->request->get('parent');
        $categoryApi = $this->get('zikula_categories_module.api.category');

        $cat = $categoryApi->getCategoryById($cid);
        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        // prevent deletion if category is already used
        if (!$processingHelper->mayCategoryBeDeletedOrMoved($cat)) {
            return new BadDataResponse($this->__f('Error! Category %s can not be deleted, because it is already used.', ['%s' => $cat['name']]));
        }

        $this->get('zikula_categories_module.copy_and_move_helper')->moveSubCategoriesByPath($cat['ipath'], $parent);
        $categoryApi->deleteCategoryById($cat['id']);

        // need to re-render new parents node
        $newParent = $categoryApi->getCategoryById($parent);

        $categories = $categoryApi->getSubCategories($newParent['id'], true, true, true, true, true);

        $leafStatus = [
            'leaf' => [],
            'noleaf' => []
        ];
        foreach ($categories as $c) {
            if ($c['is_leaf']) {
                $leafStatus['leaf'][] = $c['id'];
            } else {
                $leafStatus['noleaf'][] = $c['id'];
            }
        }

        $result = [
            'action' => 'deleteandmovesubs',
            'cid' => $cid,
            'parent' => $newParent['id'],
            'leafstatus' => $leafStatus,
            'result' => true
        ];

        return new AjaxResponse($result);
    }

    /**
     * @Route("/deletedialog", options={"expose"=true})
     * @Method("POST")
     *
     * Display a dialog to get the category to move subcategories to once the parent has been deleted
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     */
    public function deletedialogAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_DELETE)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }

        $cid = $request->request->get('cid');

        $allCats = $this->get('zikula_categories_module.api.category')->getSubCategories(1, true, true, true, false, true, $cid);
        $selector = $this->get('zikula_categories_module.html_tree_helper')->getSelector($allCats, 'id', '0', 'category[parent_id]', 0, '', 0, '', false, false, true, 1, false, 'form-control');

        $result = [
            'result' => $this->renderView('@ZikulaCategoriesModule/Ajax/delete.html.twig', [
                'categorySelector' => $selector
            ])
        ];

        return new AjaxResponse($result);
    }

    /**
     * @Route("/activate", options={"expose"=true})
     * @Method("POST")
     *
     * Activate a category
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     */
    public function activateAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }

        $entityManager = $this->get('doctrine')->getManager();

        $cid = $request->request->get('cid');
        $cat = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cid);
        $cat['status'] = 'A';
        $entityManager->flush();

        $result = [
            'action' => 'activate',
            'cid' => $cid,
            'result' => true
        ];

        return new AjaxResponse($result);
    }

    /**
     * @Route("/deactivate", options={"expose"=true})
     * @Method("POST")
     *
     * Deactivate a category
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     */
    public function deactivateAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_EDIT)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }

        $entityManager = $this->get('doctrine')->getManager();

        $cid = $request->request->get('cid');
        $cat = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $cid);
        $cat['status'] = 'I';
        $entityManager->flush();

        $result = [
            'action' => 'deactivate',
            'cid' => $cid,
            'result' => true
        ];

        return new AjaxResponse($result);
    }

    /**
     * @Route("/save", options={"expose"=true})
     * @Method("POST")
     *
     * Save a category
     *
     * @param Request $request
     *
     * @return AjaxResponse ajax response object
     */
    public function saveAction(Request $request)
    {
        $mode = $request->request->get('mode', 'new');
        $accessLevel = $mode == 'edit' ? ACCESS_EDIT : ACCESS_ADD;
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', $accessLevel)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
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

        $processingHelper = $this->get('zikula_categories_module.category_processing_helper');

        $valid = $processingHelper->validateCategoryData($data);
        if (!$valid) {
            $request->request->set('cid', (isset($data['cid']) ? $data['cid'] : 0));
            $request->request->set('parent', $data['parent_id']);
            $request->request->set('mode', $mode);

            return $this->editAction($request);
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
        if ($mode == 'edit') {
            $category = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $data['id']);
        } else {
            $category = new CategoryEntity();
        }
        $prevCategoryName = $category['name'];
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

        // since a name change will change the object path, we must rebuild it here
        if ($prevCategoryName != $category['name']) {
            $this->get('zikula_categories_module.path_builder_helper')->rebuildPaths('path', 'name', $category['id']);
        }

        $categories = $this->get('zikula_categories_module.api.category')->getSubCategories($category['id'], true, true, true, true, true);
        $node = $this->get('zikula_categories_module.js_tree_helper')->getJsTreeNodeFromCategoryArray([0 => $category]);

        $leafStatus = [
            'leaf' => [],
            'noleaf' => []
        ];
        foreach ($categories as $c) {
            if ($c['is_leaf']) {
                $leafStatus['leaf'][] = $c['id'];
            } else {
                $leafStatus['noleaf'][] = $c['id'];
            }
        }

        $result = [
            'action' => $mode == 'edit' ? 'edit' : 'add',
            'cid' => $category['id'],
            'parent' => $category['parent']->getId(),
            'node' => $node,
            'leafstatus' => $leafStatus,
            'result' => true
        ];

        return new AjaxResponse($result);
    }
}

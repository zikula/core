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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Zikula\CategoriesModule\GenericUtil;
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

        $entityManager = $this->get('doctrine.orm.entity_manager');

        foreach ($tree as $catData) {
            if (empty($catData)) {
                continue;
            }
            /** @var \Zikula\CategoriesModule\Entity\CategoryEntity $category */
            $category = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $catData['id']);
            $category->setSort_value($catData['lineno']);
            if (!empty($catData['parent'])) {
                /** @var \Zikula\CategoriesModule\Entity\CategoryEntity $parent */
                $parent = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $catData['parent']);
                $category->setParent($parent);
                // reset paths
                $category->setPath(GenericUtil::processCategoryPath($parent->getPath(), $category->getName()));
                $category->setIPath(GenericUtil::processCategoryIPath($parent->getIPath(), $category->getId()));
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
            $editCat = CategoryUtil::getCategoryByID($cid);
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
            'result' => $this->get('twig')->render('@ZikulaCategoriesModule/Ajax/edit.html.twig', $templateParameters),
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

        $cat = CategoryUtil::getCategoryByID($cid);
        CategoryUtil::copyCategoriesByPath($cat['ipath'], $parent);

        $copyParent = CategoryUtil::getCategoryByID($cat['parent_id']);

        $categories = CategoryUtil::getSubCategories($copyParent['id'], true, true, true, true, true);

        $entityManager = $this->get('doctrine.orm.entity_manager');

        // get the last added category in the parent
        $qb = $entityManager->createQueryBuilder();
        $category = $qb->select('c')
            ->from('Zikula\CategoriesModule\Entity\CategoryEntity', 'c')
            ->where('c.parent = :parent_id')
            ->setParameter('parent_id', $parent)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
        // create jsTree node
        $node = CategoryUtil::getJsTreeNodeFromCategory($category);

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
        $cat = CategoryUtil::getCategoryByID($cid);

        CategoryUtil::deleteCategoriesByPath($cat['ipath']);

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

        $cat = CategoryUtil::getCategoryByID($cid);

        CategoryUtil::moveSubCategoriesByPath($cat['ipath'], $parent);
        CategoryUtil::deleteCategoryByID($cat['id']);

        // need to re-render new parents node
        $newParent = CategoryUtil::getCategoryByID($parent);

        $categories = CategoryUtil::getSubCategories($newParent['id'], true, true, true, true, true);

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

        $allCats = CategoryUtil::getSubCategories(1, true, true, true, false, true, $cid);
        $selector = CategoryUtil::getSelector_Categories($allCats, 'id', '0', 'category[parent_id]', 0, '', 0, '', false, false, true, 1, false, 'form-control');

        $templateParameters = [
            'categorySelector' => $selector
        ];

        $result = [
            'result' => $this->get('twig')->render('@ZikulaCategoriesModule/Ajax/delete.html.twig', $templateParameters)
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

        $entityManager = $this->get('doctrine.orm.entity_manager');

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

        $entityManager = $this->get('doctrine.orm.entity_manager');

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

        $valid = GenericUtil::validateCategoryData($data);
        if (!$valid) {
            $request->request->set('cid', (isset($data['cid']) ? $data['cid'] : 0));
            $request->request->set('parent', $data['parent_id']);
            $request->request->set('mode', $mode);

            return $this->editAction($request);
        }

        // process name
        $data['name'] = GenericUtil::processCategoryName($data['name']);

        // process parent
        $data['parent'] = GenericUtil::processCategoryParent($data['parent_id']);
        unset($data['parent_id']);

        // process display names
        $data['display_name'] = GenericUtil::processCategoryDisplayName($data['display_name'], $data['name']);

        // save category
        $entityManager = $this->get('doctrine.orm.entity_manager');
        if ($mode == 'edit') {
            $category = $entityManager->find('ZikulaCategoriesModule:CategoryEntity', $data['id']);
        } else {
            $category = new \Zikula\CategoriesModule\Entity\CategoryEntity();
        }
        $prevCategoryName = $category['name'];
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

        // since a name change will change the object path, we must rebuild it here
        if ($prevCategoryName != $category['name']) {
            CategoryUtil::rebuildPaths('path', 'name', $category['id']);
        }

        $categories = CategoryUtil::getSubCategories($category['id'], true, true, true, true, true);
        $node = CategoryUtil::getJsTreeNodeFromCategoryArray([0 => $category]);

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

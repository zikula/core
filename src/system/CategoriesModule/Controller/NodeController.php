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
use Symfony\Component\HttpFoundation\Request;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Form\Type\CategoryType;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\BadDataResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;

/**
 * @Route("/admin/category")
 *
 * Controller for handling category registries.
 */
class NodeController extends AbstractController
{
    private $domTreeNodePrefix = 'node_';

    /**
     * @Route("/contextMenu/{action}/{id}", options={"expose"=true}, defaults={"id" = null})
     * @param Request $request
     * @param string $action
     * @param CategoryEntity $category
     * @return AjaxResponse|BadDataResponse|ForbiddenResponse
     */
    public function contextMenuAction(Request $request, $action = 'edit', CategoryEntity $category = null)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }
        if (!in_array($action, ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate'])) {
            return new BadDataResponse($this->__('Data provided was inappropriate.'));
        }
        $repo = $this->get('zikula_categories_module.category_repository');
        $mode = $request->request->get('mode', 'edit');
        $entityManager = $this->getDoctrine()->getManager();

        switch ($action) {
            case 'copy':
                $newCategory = clone $category;
                $newCategory->setName($category->getName() . 'copy');
                $displayNames = [];
                foreach ($newCategory->getDisplay_name() as $locale => $displayName) {
                    $displayNames[$locale] = $displayName . ' ' . $this->__('copy');
                }
                $newCategory->setDisplay_name($displayNames);
                $action = 'edit';
                $mode = 'new';
                $category = $newCategory;
                // intentionally no break here
            case 'edit':
                if (!isset($category)) {
                    $category = new CategoryEntity($this->get('zikula_settings_module.locale_api')->getSupportedLocales());
                    $parentId = $request->request->get('parent');
                    $mode = 'new';
                    if (!empty($parentId)) {
                        $parent = $repo->find($parentId);
                        $category->setParent($parent);
                        $category->setRoot($parent->getRoot());
                    } elseif (empty($parentId) && $request->request->has('after')) { // sibling of top-level child
                        $sibling = $repo->find($request->request->get('after'));
                        $category->setParent($sibling->getParent());
                        $category->setRoot($sibling->getRoot());
                    }
                }
                $form = $this->createForm(CategoryType::class, $category, [
                    'translator' => $this->get('translator.default'),
                    'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocales(),
                ]);
                $form->get('after')->setData($request->request->get('after', null));
                if ($form->handleRequest($request)->isValid()) {
                    $category = $form->getData();
                    $after = $form->get('after')->getData();
                    if (!empty($after)) {
                        $sibling = $repo->find($after);
                        $repo->persistAsNextSiblingOf($category, $sibling);
                    } elseif ($mode == 'new') {
                        $repo->persistAsLastChild($category);
                    } // no need to persist edited entity
                    $entityManager->flush();

                    return new AjaxResponse([
                        'node' => $category->toJson($this->domTreeNodePrefix, $request->getLocale()),
                        'mode' => $mode
                    ]);
                }
                $response = [
                    'result' => $this->renderView('@ZikulaCategoriesModule/Category/edit.html.twig', [
                        'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(null, $request->getLocale()),
                        'form' => $form->createView()
                    ]),
                    'action' => $action,
                    'id' => $category->getId(),
                    'mode' => $mode
                ];
                break;
            case 'deleteandmovechildren':
                $newParent = $repo->find($request->request->get('parent', 1));
                if ($newParent == $category->getParent()) {
                    $response = ['result' => true];
                    break;
                }
                // move the children
                foreach ($category->getChildren() as $child) {
                    if ($this->get('zikula_categories_module.category_processing_helper')->mayCategoryBeDeletedOrMoved($child)) {
                        $category->getChildren()->removeElement($child);
                        $newParent->getChildren()->add($child);
                        $child->setParent($newParent);
                    }
                }
                $entityManager->flush();
                // intentionally no break here
            case 'delete':
                $categoryId = $category->getId();
                $this->removeRecursive($category);
                $categoryRemoved = false;
                if ($category->getChildren()->count() == 0
                    && $this->get('zikula_categories_module.category_processing_helper')->mayCategoryBeDeletedOrMoved($category)) {
                    $entityManager->remove($category);
                    $categoryRemoved = true;
                }
                $entityManager->flush();
                $response = [
                    'result' => $categoryRemoved,
                    'id' => $categoryId,
                    'action' => $action,
                    'parent' => isset($newParent) ? $newParent->getId() : null
                ];
                $repo->recover();
                $this->getDoctrine()->getManager()->flush();
                break;
            case 'activate':
            case 'deactivate':
                $category->setStatus($category->getStatus() == 'A' ? 'I' : 'A');
                $entityManager->flush();
                $response = [
                    'id' => $category->getId(),
                    'parent' => $category->getParent()->getId(),
                    'action' => $action,
                    'result' => true
                ];
                break;
            default:
                $response = ['result' => true];
        }

        return new AjaxResponse($response);
    }

    /**
     * Recursive method to remove all generations below parent
     * @param CategoryEntity $parent
     */
    private function removeRecursive(CategoryEntity $parent)
    {
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($parent->getChildren() as $child) {
            if ($child->getChildren()->count() > 0) {
                $this->removeRecursive($child);
            }
            if ($this->get('zikula_categories_module.category_processing_helper')->mayCategoryBeDeletedOrMoved($child)) {
                $entityManager->remove($child);
            }
        }
    }

    /**
     * Ajax function for use on Drag and Drop of nodes.
     * @Route("/move", options={"expose"=true})
     * @param Request $request
     * @return AjaxResponse|ForbiddenResponse
     */
    public function moveAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }
        $repo = $this->get('zikula_categories_module.category_repository');
        $node = $request->request->get('node');
        $entityId = str_replace($this->domTreeNodePrefix, '', $node['id']);
        $category = $repo->find($entityId);
        if ($this->get('zikula_categories_module.category_processing_helper')->mayCategoryBeDeletedOrMoved($category)) {
            $oldParent = $request->request->get('old_parent');
            $oldPosition = (int)$request->request->get('old_position');
            $parent = $request->request->get('parent');
            $position = (int)$request->request->get('position');
            if ($oldParent == $parent) {
                $diff = $oldPosition - $position; // if $diff is positive, then node moved up
                $methodName = $diff > 0 ? 'moveUp' : 'moveDown';
                $repo->$methodName($category, abs($diff));
            } else {
                $parentEntity = $repo->find(str_replace($this->domTreeNodePrefix, '', $parent));
                $children = $repo->children($parentEntity);
                $repo->persistAsNextSiblingOf($category, $children[$position - 1]);
            }
            $this->getDoctrine()->getManager()->flush();

            return new AjaxResponse(['result' => true]);
        } else {
            return new AjaxResponse(['result' => false]);
        }
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;
use Zikula\CategoriesModule\Form\Type\CategoryType;
use Zikula\CategoriesModule\Helper\CategoryProcessingHelper;
use Zikula\Core\Controller\AbstractController;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

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
     *
     * @param Request $request
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryProcessingHelper $processingHelper
     * @param LocaleApiInterface $localeApi
     * @param string $action
     * @param CategoryEntity $category
     *
     * @return JsonResponse
     */
    public function contextMenuAction(
        Request $request,
        CategoryRepositoryInterface $categoryRepository,
        CategoryProcessingHelper $processingHelper,
        LocaleApiInterface $localeApi,
        $action = 'edit',
        CategoryEntity $category = null
    ) {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('No permission for this action'), Response::HTTP_FORBIDDEN);
        }
        if (!in_array($action, ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate'])) {
            return $this->json($this->__('Data provided was inappropriate.'), Response::HTTP_BAD_REQUEST);
        }
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
                    $category = new CategoryEntity($localeApi->getSupportedLocales());
                    $parentId = $request->request->get('parent');
                    $mode = 'new';
                    if (!empty($parentId)) {
                        $parent = $categoryRepository->find($parentId);
                        $category->setParent($parent);
                        $category->setRoot($parent->getRoot());
                    } elseif (empty($parentId) && $request->request->has('after')) { // sibling of top-level child
                        $sibling = $categoryRepository->find($request->request->get('after'));
                        $category->setParent($sibling->getParent());
                        $category->setRoot($sibling->getRoot());
                    }
                }
                $form = $this->createForm(CategoryType::class, $category, [
                    'locales' => $localeApi->getSupportedLocales()
                ]);
                $form->get('after')->setData($request->request->get('after', null));
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $category = $form->getData();
                    $after = $form->get('after')->getData();
                    if (!empty($after)) {
                        $sibling = $categoryRepository->find($after);
                        $categoryRepository->persistAsNextSiblingOf($category, $sibling);
                    } elseif ('new' === $mode) {
                        $categoryRepository->persistAsLastChild($category);
                    } // no need to persist edited entity
                    $entityManager->flush();

                    return $this->json([
                        'node' => $category->toJson($this->domTreeNodePrefix, $request->getLocale()),
                        'mode' => $mode
                    ]);
                }
                $response = [
                    'result' => $this->renderView('@ZikulaCategoriesModule/Category/edit.html.twig', [
                        'locales' => $localeApi->getSupportedLocaleNames(null, $request->getLocale()),
                        'form' => $form->createView()
                    ]),
                    'action' => $action,
                    'id' => $category->getId(),
                    'mode' => $mode
                ];
                break;
            case 'deleteandmovechildren':
                $newParent = $categoryRepository->find($request->request->get('parent', 1));
                if ($newParent === $category->getParent()) {
                    $response = ['result' => true];
                    break;
                }
                // move the children
                foreach ($category->getChildren() as $child) {
                    if ($processingHelper->mayCategoryBeDeletedOrMoved($child)) {
                        $category->getChildren()->removeElement($child);
                        $newParent->getChildren()->add($child);
                        $child->setParent($newParent);
                    }
                }
                $entityManager->flush();
                // intentionally no break here
            case 'delete':
                $categoryId = $category->getId();
                $this->removeRecursive($category, $processingHelper);
                $categoryRemoved = false;
                if (0 === $category->getChildren()->count()
                    && $processingHelper->mayCategoryBeDeletedOrMoved($category)) {
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
                $categoryRepository->recover();
                $this->getDoctrine()->getManager()->flush();
                break;
            case 'activate':
            case 'deactivate':
                $category->setStatus('A' === $category->getStatus() ? 'I' : 'A');
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

        return $this->json($response);
    }

    /**
     * Recursive method to remove all generations below parent.
     *
     * @param CategoryEntity $parent
     * @param CategoryProcessingHelper $processingHelper
     */
    private function removeRecursive(CategoryEntity $parent, CategoryProcessingHelper $processingHelper)
    {
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($parent->getChildren() as $child) {
            if ($child->getChildren()->count() > 0) {
                $this->removeRecursive($child);
            }
            if ($processingHelper->mayCategoryBeDeletedOrMoved($child)) {
                $entityManager->remove($child);
            }
        }
    }

    /**
     * Ajax function for use on Drag and Drop of nodes.
     *
     * @Route("/move", options={"expose"=true})
     *
     * @param Request $request
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryProcessingHelper $processingHelper
     *
     * @return JsonResponse
     */
    public function moveAction(Request $request, CategoryRepositoryInterface $categoryRepository, CategoryProcessingHelper $processingHelper)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('No permission for this action'), Response::HTTP_FORBIDDEN);
        }
        $node = $request->request->get('node');
        $entityId = str_replace($this->domTreeNodePrefix, '', $node['id']);
        $category = $categoryRepository->find($entityId);
        if ($processingHelper->mayCategoryBeDeletedOrMoved($category)) {
            $oldParent = $request->request->get('old_parent');
            $oldPosition = (int)$request->request->get('old_position');
            $parent = $request->request->get('parent');
            $position = (int)$request->request->get('position');
            if ($oldParent === $parent) {
                $diff = $oldPosition - $position; // if $diff is positive, then node moved up
                $methodName = $diff > 0 ? 'moveUp' : 'moveDown';
                $categoryRepository->{$methodName}($category, abs($diff));
            } else {
                $parentEntity = $categoryRepository->find(str_replace($this->domTreeNodePrefix, '', $parent));
                $children = $categoryRepository->children($parentEntity);
                $categoryRepository->persistAsNextSiblingOf($category, $children[$position - 1]);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->json(['result' => true]);
        }

        return $this->json(['result' => false]);
    }
}

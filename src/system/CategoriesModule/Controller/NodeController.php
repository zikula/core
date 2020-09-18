<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\Repository\CategoryRepository;
use Zikula\CategoriesModule\Form\Type\CategoryType;
use Zikula\CategoriesModule\Helper\CategoryProcessingHelper;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

/**
 * Class NodeController
 *
 * @Route("/admin/category")
 * @PermissionCheck("admin")
 */
class NodeController extends AbstractController
{
    /**
     * @var string
     */
    private $domTreeNodePrefix = 'node_';

    /**
     * @Route("/contextMenu/{action}/{id}", options={"expose"=true}, defaults={"id" = null})
     */
    public function contextMenu(
        Request $request,
        CategoryRepository $categoryRepository,
        CategoryProcessingHelper $processingHelper,
        LocaleApiInterface $localeApi,
        string $action = 'edit',
        CategoryEntity $category = null
    ): JsonResponse {
        if (!in_array($action, ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate'])) {
            return $this->json($this->trans('Data provided was inappropriate.'), Response::HTTP_BAD_REQUEST);
        }
        $mode = $request->request->get('mode', 'edit');

        switch ($action) {
            case 'copy':
                if (!isset($category)) {
                    $category = new CategoryEntity($localeApi->getSupportedLocales());
                }
                $newCategory = clone $category;
                $newCategory->setName($category->getName() . 'copy');
                $displayNames = [];
                foreach ($newCategory->getDisplayName() as $locale => $displayName) {
                    $displayNames[$locale] = $displayName . ' ' . $this->trans('copy');
                }
                $newCategory->setDisplayName($displayNames);
                $action = 'edit';
                $mode = 'new';
                $category = $newCategory;
                // intentionally no break here
                // no break
            case 'edit':
                if (!isset($category)) {
                    $category = new CategoryEntity($localeApi->getSupportedLocales());
                    $parentId = $request->request->get('parent');
                    $mode = 'new';
                    if (!empty($parentId)) {
                        /** @var CategoryEntity $parent */
                        $parent = $categoryRepository->find($parentId);
                        $category->setParent($parent);
                        $category->setRoot($parent->getRoot());
                    } elseif (empty($parentId) && $request->request->has('after')) { // sibling of top-level child
                        /** @var CategoryEntity $sibling */
                        $sibling = $categoryRepository->find($request->request->get('after'));
                        $category->setParent($sibling->getParent());
                        $category->setRoot($sibling->getRoot());
                    }
                }
                $form = $this->createForm(CategoryType::class, $category, [
                    'locales' => $localeApi->getSupportedLocales()
                ]);
                $form->get('after')->setData($request->request->get('after'));
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
                    $this->getDoctrine()->getManager()->flush();

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
                /** @var CategoryEntity $newParent */
                $newParent = $categoryRepository->find($request->request->get('parent', 1));
                if (null === $newParent || $newParent === $category->getParent()) {
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
                $this->getDoctrine()->getManager()->flush();
                // intentionally no break here
                // no break
            case 'delete':
                $categoryId = $category->getId();
                $this->removeRecursive($category, $processingHelper);
                $categoryRemoved = false;
                if (0 === $category->getChildren()->count()
                    && $processingHelper->mayCategoryBeDeletedOrMoved($category)) {
                    $this->getDoctrine()->getManager()->remove($category);
                    $categoryRemoved = true;
                }
                $this->getDoctrine()->getManager()->flush();
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
                $this->getDoctrine()->getManager()->flush();
                $response = [
                    'id' => $category->getId(),
                    'parent' => null !== $category->getParent() ? $category->getParent()->getId() : null,
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
     */
    private function removeRecursive(CategoryEntity $parent, CategoryProcessingHelper $processingHelper): void
    {
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($parent->getChildren() as $child) {
            if ($child->getChildren()->count() > 0) {
                $this->removeRecursive($child, $processingHelper);
            }
            if ($processingHelper->mayCategoryBeDeletedOrMoved($child)) {
                $entityManager->remove($child);
            }
        }
    }

    /**
     * Ajax function for use on drag and drop of nodes.
     * @Route("/move", options={"expose"=true})
     */
    public function move(
        Request $request,
        CategoryRepository $categoryRepository,
        CategoryProcessingHelper $processingHelper
    ): JsonResponse {
        $node = $request->request->get('node');
        $entityId = str_replace($this->domTreeNodePrefix, '', $node['id']);
        /** @var CategoryEntity $category */
        $category = $categoryRepository->find($entityId);
        if (!$processingHelper->mayCategoryBeDeletedOrMoved($category)) {
            return $this->json(['result' => false]);
        }

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
            if (1 > $position) {
                $categoryRepository->persistAsFirstChildOf($category, $parentEntity);
            } else {
                $children = $categoryRepository->children($parentEntity);
                $categoryRepository->persistAsNextSiblingOf($category, $children[$position - 1]);
            }
        }
        $this->getDoctrine()->getManager()->flush();

        return $this->json(['result' => true]);
    }
}

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

namespace Zikula\CategoriesBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\CategoriesBundle\Entity\CategoryEntity;
use Zikula\CategoriesBundle\Form\Type\CategoryType;
use Zikula\CategoriesBundle\Helper\CategoryProcessingHelper;
use Zikula\CategoriesBundle\Repository\CategoryRepository;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;

#[Route('/categories/admin/category')]
#[PermissionCheck('admin')]
class NodeController extends AbstractController
{
    private string $domTreeNodePrefix = 'node_';

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Route('/contextMenu/{action}/{id}', name: 'zikulacategoriesbundle_node_contextmenu', defaults: ['id' => null], options: ['expose' => true])]
    public function contextMenu(
        Request $request,
        ManagerRegistry $doctrine,
        CategoryRepository $categoryRepository,
        CategoryProcessingHelper $processingHelper,
        LocaleApiInterface $localeApi,
        string $action = 'edit',
        CategoryEntity $category = null
    ): JsonResponse {
        if (!in_array($action, ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate'])) {
            return $this->json($this->translator->trans('Data provided was inappropriate.'), Response::HTTP_BAD_REQUEST);
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
                    $displayNames[$locale] = $displayName . ' ' . $this->translator->trans('copy');
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
                    $doctrine->getManager()->flush();

                    return $this->json([
                        'node' => $category->toJson($this->domTreeNodePrefix, $request->getLocale()),
                        'mode' => $mode
                    ]);
                }
                $response = [
                    'result' => $this->renderView('@ZikulaCategories/Category/edit.html.twig', [
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
                $doctrine->getManager()->flush();
                // intentionally no break here
                // no break
            case 'delete':
                $categoryId = $category->getId();
                $this->removeRecursive($category, $doctrine, $processingHelper);
                $categoryRemoved = false;
                if (0 === $category->getChildren()->count()
                    && $processingHelper->mayCategoryBeDeletedOrMoved($category)) {
                    $doctrine->getManager()->remove($category);
                    $categoryRemoved = true;
                }
                $doctrine->getManager()->flush();
                $response = [
                    'result' => $categoryRemoved,
                    'id' => $categoryId,
                    'action' => $action,
                    'parent' => isset($newParent) ? $newParent->getId() : null
                ];
                $categoryRepository->recover();
                $doctrine->getManager()->flush();
                break;
            case 'activate':
            case 'deactivate':
                $category->setStatus('A' === $category->getStatus() ? 'I' : 'A');
                $doctrine->getManager()->flush();
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
    private function removeRecursive(CategoryEntity $parent, ManagerRegistry $doctrine, CategoryProcessingHelper $processingHelper): void
    {
        $entityManager = $doctrine->getManager();
        foreach ($parent->getChildren() as $child) {
            if ($child->getChildren()->count() > 0) {
                $this->removeRecursive($child, $doctrine, $processingHelper);
            }
            if ($processingHelper->mayCategoryBeDeletedOrMoved($child)) {
                $entityManager->remove($child);
            }
        }
    }

    /**
     * Ajax function for use on drag and drop of nodes.
     */
    #[Route('/move', name: 'zikulacategoriesbundle_node_move', options: ['expose' => true])]
    public function move(
        Request $request,
        ManagerRegistry $doctrine,
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
        $oldPosition = (int) $request->request->get('old_position');
        $parent = $request->request->get('parent');
        $position = (int) $request->request->get('position');
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
        $doctrine->getManager()->flush();

        return $this->json(['result' => true]);
    }
}

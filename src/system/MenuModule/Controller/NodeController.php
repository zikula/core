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

namespace Zikula\MenuModule\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\MenuModule\Entity\MenuItemEntity;
use Zikula\MenuModule\Entity\Repository\MenuItemRepository;
use Zikula\MenuModule\Form\Type\MenuItemType;

/**
 * Class NodeController
 *
 * @Route("/node")
 */
class NodeController extends AbstractController
{
    /**
     * @var string
     */
    private $domTreeNodePrefix = 'node_';

    /**
     * @Route("/contextMenu/{action}/{id}", options={"expose"=true, "i18n"=false})
     */
    public function contextMenu(
        Request $request,
        MenuItemRepository $menuItemRepository,
        string $action = 'edit',
        MenuItemEntity $menuItemEntity = null
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->trans('No permission for this action'), Response::HTTP_FORBIDDEN);
        }
        if (!in_array($action, ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate'])) {
            return $this->json($this->trans('Data provided was inappropriate.'), Response::HTTP_BAD_REQUEST);
        }
        $mode = $request->request->get('mode', 'edit');

        switch ($action) {
            case 'edit':
                if (!isset($menuItemEntity)) {
                    $menuItemEntity = new MenuItemEntity();
                    $parentId = $request->request->get('parent');
                    $mode = 'new';
                    if (!empty($parentId)) {
                        /** @var MenuItemEntity $parent */
                        $parent = $menuItemRepository->find($request->request->get('parent'));
                        $menuItemEntity->setParent($parent);
                        $menuItemEntity->setRoot($parent->getRoot());
                    } elseif (empty($parentId) && $request->request->has('after')) { // sibling of top-level child
                        /** @var MenuItemEntity $sibling */
                        $sibling = $menuItemRepository->find($request->request->get('after'));
                        $menuItemEntity->setParent($sibling->getParent());
                        $menuItemEntity->setRoot($sibling->getRoot());
                    }
                }
                $form = $this->createForm(MenuItemType::class, $menuItemEntity);
                $form->get('after')->setData($request->request->get('after'));
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $menuItemEntity = $form->getData();
                    $after = $form->get('after')->getData();
                    if (!empty($after)) {
                        $sibling = $menuItemRepository->find($after);
                        $menuItemRepository->persistAsNextSiblingOf($menuItemEntity, $sibling);
                    } elseif ('new' === $mode) {
                        $menuItemRepository->persistAsLastChild($menuItemEntity);
                    } // no need to persist edited entity
                    $this->getDoctrine()->getManager()->flush();

                    return $this->json([
                        'node' => $menuItemEntity->toJson($this->domTreeNodePrefix),
                        'mode' => $mode
                    ]);
                }
                $response = [
                    'result' => $this->renderView('@ZikulaMenuModule/Menu/edit.html.twig', [
                        'form' => $form->createView()
                    ]),
                    'action' => $action,
                    'id' => $menuItemEntity->getId(),
                    'mode' => $mode
                ];
                break;
            case 'delete':
                $id = $menuItemEntity->getId();
                $this->getDoctrine()->getManager()->remove($menuItemEntity);
                $this->getDoctrine()->getManager()->flush();
                $response = [
                    'id' => $id,
                    'action' => $action,
                ];
                break;
            default:
                $response = ['result' => true];
        }

        return $this->json($response);
    }

    /**
     * Ajax function for use on drag and drop of nodes.
     * @Route("/move", options={"expose"=true})
     */
    public function move(
        Request $request,
        MenuItemRepository $menuItemRepository
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->trans('No permission for this action'), Response::HTTP_FORBIDDEN);
        }
        $node = $request->request->get('node');
        $entityId = str_replace($this->domTreeNodePrefix, '', $node['id']);
        $menuItemEntity = $menuItemRepository->find($entityId);
        $oldParent = $request->request->get('old_parent');
        $oldPosition = (int) $request->request->get('old_position');
        $parent = $request->request->get('parent');
        $position = (int) $request->request->get('position');
        if ($oldParent === $parent) {
            $diff = $oldPosition - $position; // if $diff is positive, then node moved up
            $methodName = $diff > 0 ? 'moveUp' : 'moveDown';
            $menuItemRepository->{$methodName}($menuItemEntity, abs($diff));
        } else {
            $parentEntity = $menuItemRepository->find(str_replace($this->domTreeNodePrefix, '', $parent));
            if (1 > $position) {
                $menuItemRepository->persistAsFirstChildOf($menuItemEntity, $parentEntity);
            } else {
                $children = $menuItemRepository->children($parentEntity);
                $menuItemRepository->persistAsNextSiblingOf($menuItemEntity, $children[$position - 1]);
            }
        }
        $this->getDoctrine()->getManager()->flush();

        return $this->json(['result' => true]);
    }
}

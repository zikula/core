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

namespace Zikula\MenuModule\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Core\Controller\AbstractController;
use Zikula\MenuModule\Entity\MenuItemEntity;
use Zikula\MenuModule\Form\Type\MenuItemType;

/**
 * Class NodeController
 * @Route("/node")
 */
class NodeController extends AbstractController
{
    private $domTreeNodePrefix = 'node_';

    /**
     * @Route("/contextMenu/{action}/{id}", options={"expose"=true, "i18n"=false})
     * @param Request $request
     * @param string $action
     * @param MenuItemEntity $menuItemEntity
     * @return JsonResponse
     */
    public function contextMenuAction(Request $request, $action = 'edit', MenuItemEntity $menuItemEntity = null)
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('No permission for this action'), Response::HTTP_FORBIDDEN);
        }
        if (!in_array($action, ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate'])) {
            return $this->json($this->__('Data provided was inappropriate.'), Response::HTTP_BAD_REQUEST);
        }
        $repo = $this->get('doctrine')->getRepository(MenuItemEntity::class);
        $mode = $request->request->get('mode', 'edit');

        switch ($action) {
            case 'edit':
                if (!isset($menuItemEntity)) {
                    $menuItemEntity = new MenuItemEntity();
                    $parentId = $request->request->get('parent');
                    $mode = 'new';
                    if (!empty($parentId)) {
                        $parent = $repo->find($request->request->get('parent'));
                        $menuItemEntity->setParent($parent);
                        $menuItemEntity->setRoot($parent->getRoot());
                    } elseif (empty($parentId) && $request->request->has('after')) { // sibling of top-level child
                        $sibling = $repo->find($request->request->get('after'));
                        $menuItemEntity->setParent($sibling->getParent());
                        $menuItemEntity->setRoot($sibling->getRoot());
                    }
                }
                $form = $this->createForm(MenuItemType::class, $menuItemEntity);
                $form->get('after')->setData($request->request->get('after', null));
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $menuItemEntity = $form->getData();
                    $after = $form->get('after')->getData();
                    if (!empty($after)) {
                        $sibling = $repo->find($after);
                        $repo->persistAsNextSiblingOf($menuItemEntity, $sibling);
                    } elseif ('new' === $mode) {
                        $repo->persistAsLastChild($menuItemEntity);
                    } // no need to persist edited entity
                    $this->get('doctrine')->getManager()->flush();

                    return $this->json([
                        'node' => $menuItemEntity->toJson($this->domTreeNodePrefix),
                        'mode' => $mode
                    ]);
                }
                $response = [
                    'result' => $this->get('twig')->render('@ZikulaMenuModule/Menu/edit.html.twig', [
                        'form' => $form->createView()
                    ]),
                    'action' => $action,
                    'id' => $menuItemEntity->getId(),
                    'mode' => $mode
                ];
                break;
            case 'delete':
                $id = $menuItemEntity->getId();
                $this->get('doctrine')->getManager()->remove($menuItemEntity);
                $this->get('doctrine')->getManager()->flush();
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
     * Ajax function for use on Drag and Drop of nodes.
     * @Route("/move", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function moveAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('No permission for this action'), Response::HTTP_FORBIDDEN);
        }
        $repo = $this->get('doctrine')->getRepository(MenuItemEntity::class);
        $node = $request->request->get('node');
        $entityId = str_replace($this->domTreeNodePrefix, '', $node['id']);
        $menuItemEntity = $repo->find($entityId);
        $oldParent = $request->request->get('old_parent');
        $oldPosition = (int)$request->request->get('old_position');
        $parent = $request->request->get('parent');
        $position = (int)$request->request->get('position');
        if ($oldParent === $parent) {
            $diff = $oldPosition - $position; // if $diff is positive, then node moved up
            $methodName = $diff > 0 ? 'moveUp' : 'moveDown';
            $repo->{$methodName}($menuItemEntity, abs($diff));
        } else {
            $parentEntity = $repo->find(str_replace($this->domTreeNodePrefix, '', $parent));
            $children = $repo->children($parentEntity);
            $repo->persistAsNextSiblingOf($menuItemEntity, $children[$position - 1]);
        }
        $this->get('doctrine')->getManager()->flush();

        return $this->json(['result' => true]);
    }
}

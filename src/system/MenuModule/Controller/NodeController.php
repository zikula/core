<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\BadDataResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
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
     * @return AjaxResponse|BadDataResponse|ForbiddenResponse
     */
    public function contextMenuAction(Request $request, $action = 'edit', MenuItemEntity $menuItemEntity = null)
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }
        if (!in_array($action, ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate'])) {
            return new BadDataResponse($this->__('Data provided was inappropriate.'));
        }
        $repo = $this->get('zikula_menu_module.menu_item_repository');
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
                $form = $this->createForm(MenuItemType::class, $menuItemEntity, [
                    'translator' => $this->getTranslator(),
                ]);
                $form->get('after')->setData($request->request->get('after', null));
                if ($form->handleRequest($request)->isValid()) {
                    $menuItemEntity = $form->getData();
                    $after = $form->get('after')->getData();
                    if (!empty($after)) {
                        $sibling = $repo->find($after);
                        $repo->persistAsNextSiblingOf($menuItemEntity, $sibling);
                    } elseif ('new' == $mode) {
                        $repo->persistAsLastChild($menuItemEntity);
                    } // no need to persist edited entity
                    $this->get('doctrine')->getManager()->flush();

                    return new AjaxResponse([
                        'node' => $menuItemEntity->toJson($this->domTreeNodePrefix),
                        'mode' => $mode
                    ]);
                }
                $response = [
                    'result' => $this->get('templating')->render('@ZikulaMenuModule/Menu/edit.html.twig', [
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

        return new AjaxResponse($response);
    }

    /**
     * Ajax function for use on Drag and Drop of nodes.
     * @Route("/move", options={"expose"=true})
     * @param Request $request
     * @return AjaxResponse|ForbiddenResponse
     */
    public function moveAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }
        $repo = $this->get('zikula_menu_module.menu_item_repository');
        $node = $request->request->get('node');
        $entityId = str_replace($this->domTreeNodePrefix, '', $node['id']);
        $menuItemEntity = $repo->find($entityId);
        $oldParent = $request->request->get('old_parent');
        $oldPosition = (int)$request->request->get('old_position');
        $parent = $request->request->get('parent');
        $position = (int)$request->request->get('position');
        if ($oldParent == $parent) {
            $diff = $oldPosition - $position; // if $diff is positive, then node moved up
            $methodName = $diff > 0 ? 'moveUp' : 'moveDown';
            $repo->$methodName($menuItemEntity, abs($diff));
        } else {
            $parentEntity = $repo->find(str_replace($this->domTreeNodePrefix, '', $parent));
            $children = $repo->children($parentEntity);
            $repo->persistAsNextSiblingOf($menuItemEntity, $children[$position - 1]);
        }
        $this->get('doctrine')->getManager()->flush();

        return new AjaxResponse(['result' => true]);
    }
}

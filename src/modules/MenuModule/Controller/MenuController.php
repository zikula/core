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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\BadDataResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
use Zikula\MenuModule\Entity\MenuItemEntity;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class MenuController
 * @Route("/admin")
 */
class MenuController extends AbstractController
{
    private $domTreeNodePrefix = 'node_';

    /**
     * @Route("/list")
     * @Template
     * @Theme("admin")
     * @param Request $request
     * @return array
     */
    public function listAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $repo = $this->get('zikula_menu_module.menu_item_repository');
        $rootNodes = $repo->getRootNodes();
//        $children = $repo->getChildren();
//        $childrenHierarchy = $repo->childrenHierarchy();

        return [
            'rootNodes' => $rootNodes
        ];
    }

    /**
     * @Route("/view/{id}")
     * @Template
     * @Theme("admin")
     * @param MenuItemEntity $menuItemEntity
     * @return array
     * @see https://jstree.com/
     * @see https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/tree.md
     */
    public function viewAction(MenuItemEntity $menuItemEntity)
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN) || null !== $menuItemEntity->getParent()) {
            throw new AccessDeniedException();
        }
        $repo = $this->get('zikula_menu_module.menu_item_repository');
        $htmlTree = $repo->childrenHierarchy(
            $menuItemEntity, /* node to start from */
            false, /* false: load all children, true: only direct */
            [
                'decorate' => true,
                'representationField' => 'title',
                'html' => true,
                'childOpen' => function ($node) {
                    return '<li class="jstree-open" id="' . $this->domTreeNodePrefix . $node['id'] . '" data-entity-id="' . $node['id'] . '">';
                }
            ]
        );
//        $options = array(
//            'decorate' => true,
//            'rootOpen' => '<ul>',
//            'rootClose' => '</ul>',
//            'childOpen' => '<li>',
//            'childClose' => '</li>',
//            'nodeDecorator' => function($node) {
//                return '<a href="/page/'.$node['slug'].'">'.$node[$field].'</a>';
//            }
//        );

        return [
            'menu' => $menuItemEntity,
            'tree' => $htmlTree
        ];
    }

    /**
     * @Route("/contextMenu/{action}/{id}", options={"expose"=true})
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
                    } elseif (empty($parent) && $request->request->has('after')) { // sibling of top-level child
                        $sibling = $repo->find($request->request->get('after'));
                        $menuItemEntity->setParent($sibling->getParent());
                        $menuItemEntity->setRoot($sibling->getRoot());
                    }
                }
                $form = $this->createForm('Zikula\MenuModule\Form\Type\MenuItemType', $menuItemEntity, [
                    'translator' => $this->get('translator.default'),
                ]);
                $form->get('after')->setData($request->request->get('after', null));
                if ($form->handleRequest($request)->isValid()) {
                    $menuItemEntity = $form->getData();
                    $after = $form->get('after')->getData();
                    if (!empty($after)) {
                        $sibling = $repo->find($after);
                        $repo->persistAsNextSiblingOf($menuItemEntity, $sibling);
                    } elseif ($mode == 'new') {
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
}

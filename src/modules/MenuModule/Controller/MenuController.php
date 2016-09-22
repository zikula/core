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
    /**
     * @Route("/list")
     * @Template
     * @Theme("admin")
     * @param Request $request
     * @return array
     */
    public function listAction(Request $request)
    {
        $repo = $this->get('zikula_menu_module.menu_item_repository');
        $rootNodes = $repo->getRootNodes();
        $children = $repo->getChildren();
        $childrenHierarchy = $repo->childrenHierarchy();

        return [
            'rootNodes' => $rootNodes
        ];
    }

    /**
     * @Route("/view/{id}")
     * @Template
     * @Theme("admin")
     * @param Request $request
     * @param MenuItemEntity|null $menuItemEntity
     * @return array
     * @see https://jstree.com/
     * @see https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/tree.md
     */
    public function viewAction(Request $request, MenuItemEntity $menuItemEntity = null)
    {
        $repo = $this->get('zikula_menu_module.menu_item_repository');
        $htmlTree = $repo->childrenHierarchy(
            $menuItemEntity, /* node to start from */
            false, /* false: load all children, true: only direct */
            [
                'decorate' => true,
                'representationField' => 'title',
                'html' => true,
                'childOpen' => function ($node) {
                    return '<li class="jstree-open" id="node_' . $node['id'] . '" data-entity-id="' . $node['id'] . '">';
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
     * @Route("/contextMenu/{action}", options={"expose"=true})
     * @param Request $request
     * @param string $action
     * @return AjaxResponse|ForbiddenResponse|BadDataResponse
     */
    public function contextMenuAction(Request $request, $action = '')
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }
        if (!in_array($action, ['edit', 'delete', 'deleteandmovesubs', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'])) {
            return new BadDataResponse($this->__('Data provided was inappropriate.'));
        }

        // do something based on $action
        $entityId = $request->request->get('entityId');
        $repo = $this->get('zikula_menu_module.menu_item_repository');

        switch ($action) {
            case 'edit':
                $menuItemEntity = $repo->find($entityId);
                $form = $this->createForm('Zikula\MenuModule\Form\Type\MenuItemType', $menuItemEntity, [
                    'translator' => $this->get('translator.default'),
                ]);

                if ($form->handleRequest($request)->isValid()) {
                    $menuItemEntity = $form->getData();
                    $this->get('doctrine')->getManager()->persist($menuItemEntity); // this isn't technically required
                    $this->get('doctrine')->getManager()->flush();
//                    $this->addFlash('status', $this->__('Done! Updated the entity.'));
                    return new AjaxResponse([
                        'id' => $entityId,
                        'parent' => $menuItemEntity->getParent()->getId(),
                        'node' => $menuItemEntity->toJson()
                    ]);

//                    return $this->redirectToRoute('zikulamenumodule_menu_view', ['id' => $menuItemEntity->getRoot()->getId()]);
                }

                $response = [
                    'result' => $this->get('templating')->render('@ZikulaMenuModule/Menu/edit.html.twig', [
                        'form' => $form->createView()
                    ]),
                    'action' => $action,
                    'id' => $menuItemEntity->getId(),
                    'parent' => $menuItemEntity->getParent()->getId()
                ];

                break;
            default:
                $response = ['result' => true];
        }

        return new AjaxResponse($response);
    }
}

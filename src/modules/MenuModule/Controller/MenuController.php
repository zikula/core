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
                    return '<li class="jstree-open" id="menu_tree_' . $node['id'] . '" data-entity-id="' . $node['id'] . '">';
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
}

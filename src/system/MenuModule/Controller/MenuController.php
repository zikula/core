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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Controller\AbstractController;
use Zikula\MenuModule\Entity\MenuItemEntity;
use Zikula\MenuModule\Form\Type\DeleteMenuItemType;
use Zikula\MenuModule\Form\Type\MenuItemType;
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
                'html' => true,
                'childOpen' => function ($node) {
                    return '<li class="jstree-open" id="' . $this->domTreeNodePrefix . $node['id'] . '">';
                },
                'nodeDecorator' => function ($node) {
                    return '<a href="#">' . $node['title'] . ' (' . $node['id'] . ')</a>';
                }
            ]
        );

        return [
            'menu' => $menuItemEntity,
            'tree' => $htmlTree
        ];
    }

    /**
     * @Route("/edit/{id}", defaults={"id" = null})
     * @Theme("admin")
     * @param Request $request
     * @param MenuItemEntity|null $menuItemEntity
     * @return Response|RedirectResponse
     */
    public function editAction(Request $request, MenuItemEntity $menuItemEntity = null)
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $repo = $this->get('zikula_menu_module.menu_item_repository');
        if (!isset($menuItemEntity)) {
            $menuItemEntity = new MenuItemEntity();
        }
        $form = $this->createForm(MenuItemType::class, $menuItemEntity, [
            'translator' => $this->get('translator.default'),
        ]);
        $form->add('save', SubmitType::class, [
            'label' => $this->__('Save'),
            'icon' => 'fa-check',
            'attr' => [
                'class' => 'btn btn-success'
            ]
        ])
        ->add('cancel', SubmitType::class, [
            'label' => $this->__('Cancel'),
            'icon' => 'fa-times',
            'attr' => [
                'class' => 'btn btn-default'
            ]
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $form->get('save')->isClicked()) {
            $menuItemEntity = $form->getData();
            $repo->persistAsFirstChild($menuItemEntity);
            if (null == $menuItemEntity->getId()) {
                // create dummy child
                $dummy = new MenuItemEntity();
                $dummy->setTitle('dummy child');
                $repo->persistAsFirstChildOf($dummy, $menuItemEntity);
            }
            $this->get('doctrine')->getManager()->flush();

            return $this->redirectToRoute('zikulamenumodule_menu_list');
        }
        if ($form->isSubmitted() && $form->get('cancel')->isClicked()) {
            $this->addFlash('status', $this->__('Operation cancelled.'));

            return $this->redirectToRoute('zikulamenumodule_menu_list');
        }

        return $this->render('ZikulaMenuModule:Menu:editRootNode.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}")
     * @Template
     * @Theme("admin")
     * @param Request $request
     * @param MenuItemEntity|null $menuItemEntity
     * @return array
     */
    public function deleteAction(Request $request, MenuItemEntity $menuItemEntity)
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm(DeleteMenuItemType::class, [
            'entity' => $menuItemEntity
        ], [
            'translator' => $this->get('translator.default'),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->get('delete')->isClicked()) {
            $menuItemEntity = $form->get('entity')->getData();
            $this->get('doctrine')->getManager()->remove($menuItemEntity);
            $this->get('doctrine')->getManager()->flush();
            $this->addFlash('status', $this->__('Menu removed!'));

            return $this->redirectToRoute('zikulamenumodule_menu_list');
        }
        if ($form->isSubmitted() && $form->get('cancel')->isClicked()) {
            $this->addFlash('status', $this->__('Operation cancelled.'));

            return $this->redirectToRoute('zikulamenumodule_menu_list');
        }

        return [
            'form' => $form->createView()
        ];
    }
}

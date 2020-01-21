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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\MenuModule\Entity\MenuItemEntity;
use Zikula\MenuModule\Entity\Repository\MenuItemRepository;
use Zikula\MenuModule\Form\Type\DeleteMenuItemType;
use Zikula\MenuModule\Form\Type\MenuItemType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class MenuController
 * @Route("/admin")
 */
class MenuController extends AbstractController
{
    /**
     * @var string
     */
    private $domTreeNodePrefix = 'node_';

    /**
     * @Route("/list")
     * @Template("@ZikulaMenuModule/Menu/list.html.twig")
     * @Theme("admin")
     *
     * @throws AccessDeniedException Thrown if the user hasn't admin permissions for the module
     */
    public function listAction(
        MenuItemRepository $menuItemRepository
    ): array {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return [
            'rootNodes' => $menuItemRepository->getRootNodes()
        ];
    }

    /**
     * @Route("/view/{id}")
     * @Template("@ZikulaMenuModule/Menu/view.html.twig")
     * @Theme("admin")
     *
     * @see https://jstree.com/
     * @see https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/tree.md
     * @throws AccessDeniedException Thrown if the user hasn't admin permissions for the module
     */
    public function viewAction(
        MenuItemRepository $menuItemRepository,
        MenuItemEntity $menuItemEntity
    ): array {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN) || null !== $menuItemEntity->getParent()) {
            throw new AccessDeniedException();
        }
        $htmlTree = $menuItemRepository->childrenHierarchy(
            $menuItemEntity, /* node to start from */
            false, /* false: load all children, true: only direct */
            [
                'decorate' => true,
                'html' => true,
                'childOpen' => function($node) {
                    return '<li class="jstree-open" id="' . $this->domTreeNodePrefix . $node['id'] . '">';
                },
                'nodeDecorator' => static function($node) {
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
     *
     * @throws AccessDeniedException Thrown if the user hasn't admin permissions for the module
     */
    public function editAction(
        Request $request,
        MenuItemRepository $menuItemRepository,
        MenuItemEntity $menuItemEntity = null
    ): Response {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!isset($menuItemEntity)) {
            $menuItemEntity = new MenuItemEntity();
        }
        $form = $this->createForm(MenuItemType::class, $menuItemEntity);
        $form->add('save', SubmitType::class, [
            'label' => $this->trans('Save'),
            'icon' => 'fa-check',
            'attr' => [
                'class' => 'btn btn-success'
            ]
        ])
        ->add('cancel', SubmitType::class, [
            'label' => $this->trans('Cancel'),
            'icon' => 'fa-times',
            'attr' => [
                'class' => 'btn btn-default'
            ]
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $form->get('save')->isClicked()) {
            $menuItemEntity = $form->getData();
            $menuItemRepository->persistAsFirstChild($menuItemEntity);
            if (null === $menuItemEntity->getId()) {
                // create dummy child
                $dummy = new MenuItemEntity();
                $dummy->setTitle('dummy child');
                $menuItemRepository->persistAsFirstChildOf($dummy, $menuItemEntity);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('zikulamenumodule_menu_list');
        }
        if ($form->isSubmitted() && $form->get('cancel')->isClicked()) {
            $this->addFlash('status', 'Operation cancelled.');

            return $this->redirectToRoute('zikulamenumodule_menu_list');
        }

        return $this->render('@ZikulaMenuModule/Menu/editRootNode.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}")
     * @Template("@ZikulaMenuModule/Menu/delete.html.twig")
     * @Theme("admin")
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, MenuItemEntity $menuItemEntity)
    {
        if (!$this->hasPermission('ZikulaMenuModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm(DeleteMenuItemType::class, [
            'entity' => $menuItemEntity
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isSubmitted() && $form->get('delete')->isClicked()) {
                $menuItemEntity = $form->get('entity')->getData();
                $this->getDoctrine()->getManager()->remove($menuItemEntity);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('status', 'Done! Menu removed.');
            } elseif ($form->isSubmitted() && $form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulamenumodule_menu_list');
        }

        return [
            'form' => $form->createView()
        ];
    }
}

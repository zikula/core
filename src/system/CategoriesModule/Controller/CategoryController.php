<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\PlainResponse;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin/category")
 *
 * Controller for handling category registries.
 */
class CategoryController extends AbstractController
{
    private $domTreeNodePrefix = 'node_';

    /**
     * @Route("/list/{id}", requirements={"category" = "^[1-9]\d*$"}, defaults={"id" = 1})
     * @Template
     * @Theme("admin")
     * @param Request $request
     * @param CategoryEntity $category
     * @return array
     * @see https://jstree.com/
     * @see https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/tree.md
     */
    public function listAction(Request $request, CategoryEntity $category)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::category', 'ID::' . $category->getId(), ACCESS_EDIT)
            || !$this->hasPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $repo = $this->get('zikula_categories_module.category_repository');
        $tree = $repo->childrenHierarchy(
            $category, /* node to start from */
            false, /* false: load all children, true: only direct */
            $this->getNodeOptions($request)
        );

        return [
            'category' => $category,
            'tree' => $tree
        ];
    }

    /**
     * @Route("/edit/{category}", requirements={"category" = "^[1-9]\d*$"}, options={"expose"=true})
     * @Template
     * @Theme("admin")
     *
     * Creates or edits a category.
     *
     * @param Request $request
     *
     * @param CategoryEntity $category
     * @return array|Response|PlainResponse
     */
    public function editAction(Request $request, CategoryEntity $category = null)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (null === $category) {
            $category = new CategoryEntity($this->get('zikula_settings_module.locale_api')->getSupportedLocales());
        }
        $form = $this->createForm('Zikula\CategoriesModule\Form\Type\CategoryType',
            $category, [
                'translator' => $this->get('translator.default'),
                'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocales(),
            ]
        );
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->get('save')->isClicked() && $form->isValid()) {
                $category = $form->getData();
                $em = $this->get('doctrine')->getManager();
                $em->persist($category);
                $em->flush();
                $this->addFlash('status', $this->__('Done!'));

                return $this->redirectToRoute('zikulacategoriesmodule_category_list');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));

                return $this->redirectToRoute('zikulacategoriesmodule_category_list');
            }
        }
        $templateParameters = [
            'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(),
            'form' => $form->createView()
        ];
        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse([
                'action' => 'edit',
                'result' => $this->renderView('@ZikulaCategoriesModule/Category/edit.html.twig', $templateParameters)
            ]);
        }

        return $templateParameters;
    }

    private function getNodeOptions(Request $request)
    {
        $locale = $request->getLocale();
        $router = $this->get('router');

        return [
            'decorate' => true,
            'html' => true,
            'childOpen' => function ($node) {
                $jsTreeData = $node['status'] != 'A' ? 'data-jstree=\'{"disabled":true}\' ' : '';

                return '<li ' . $jsTreeData . 'class="jstree-open" id="' . $this->domTreeNodePrefix . $node['id'] . '">';
            },
            'nodeDecorator' => function ($node) use ($locale, $router) {
                $display = isset($node['display_name'][$locale]) ? $node['display_name'][$locale] : $node['name'];
                $title = ' title="' . $this->createTitleAttribute($node, $display, $locale) . '"';
                $href = ' href="' . $router->generate('zikulacategoriesmodule_category_edit', ['category' => $node['id']]) . '"';
                $classes = [];
                if ($node['is_locked']) {
                    $classes[] = 'locked';
                }
                if ($node['is_leaf']) {
                    $classes[] = 'leaf';
                }
                $class = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';

                return '<a' . $class . $title . $href . '>' . $display . '</a>';
            }
        ];
    }

    private function createTitleAttribute($node, $displayName, $lang)
    {
        $title = [];
        $title[] = $this->__('ID') . ': ' . $node['id'];
        $title[] = $this->__('Name') . ': ' . $node['name'];
        $title[] = $this->__('Display name') . ': ' . $displayName;
        $title[] = $this->__('Description') . ': ' . (isset($node['display_desc'][$lang]) ? $node['display_desc'][$lang] : '');
        $title[] = $this->__('Value') . ': ' . $node['value'];
        $title[] = $this->__('Active') . ': ' . ($node['status'] == 'A' ? 'Yes' : 'No');
        $title[] = $this->__('Leaf') . ': ' . ($node['is_leaf'] ? 'Yes' : 'No');
        $title[] = $this->__('Locked') . ': ' . ($node['is_locked'] ? 'Yes' : 'No');

        return implode('<br />', $title);
    }
}

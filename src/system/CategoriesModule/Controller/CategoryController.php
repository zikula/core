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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Form\Type\CategoryTreeType;
use Zikula\Core\Controller\AbstractController;
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
        $repo->recover();
        $this->getDoctrine()->getManager()->flush();
        $tree = $repo->childrenHierarchy(
            $category, /* node to start from */
            false, /* false: load all children, true: only direct */
            $this->getNodeOptions($request)
        );
        $form = $this->createFormBuilder()
            ->add('category', CategoryTreeType::class, [
                'label' => $this->__('New Parent'),
                'translator' => $this->getTranslator(),
                'includeLeaf' => false,
            ])->getForm();

        return [
            'category' => $category,
            'tree' => $tree,
            'categorySelector' => $form->createView()
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getNodeOptions(Request $request)
    {
        $locale = $request->getLocale();

        return [
            'decorate' => true,
            'html' => true,
            'childOpen' => function($node) {
                $jsTreeData = [];
                $jsTreeData['disabled'] = $node['status'] != 'A';
                $jsTreeData['type'] = $node['is_leaf'] ? 'leaf' : 'default';
                $jsTreeData = 'data-jstree="' . htmlentities(json_encode($jsTreeData)) . '" ';

                return '<li ' . $jsTreeData . 'class="jstree-open" id="' . $this->domTreeNodePrefix . $node['id'] . '">';
            },
            'nodeDecorator' => function($node) use ($locale) {
                $displayName = isset($node['display_name'][$locale]) ? $node['display_name'][$locale] : $node['name'];
                $title = ' title="' . $this->createTitleAttribute($node, $displayName, $locale) . '"';
                $classes = [];
                if ($node['is_locked']) {
                    $classes[] = 'locked';
                }
                if ($node['is_leaf']) {
                    $classes[] = 'leaf';
                }
                $class = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';

                return '<a' . $class . $title . ' href="#">' . $displayName . '</a>';
            }
        ];
    }

    /**
     * @param $node
     * @param $displayName
     * @param $locale
     * @return string
     */
    private function createTitleAttribute($node, $displayName, $locale)
    {
        $title = [];
        $title[] = $this->__('ID') . ': ' . $node['id'];
        $title[] = $this->__('Name') . ': ' . $node['name'];
        $title[] = $this->__('Display name') . ': ' . $displayName;
        $title[] = $this->__('Description') . ': ' . (isset($node['display_desc'][$locale]) ? $node['display_desc'][$locale] : '');
        $title[] = $this->__('Value') . ': ' . $node['value'];
        $title[] = $this->__('Active') . ': ' . ($node['status'] == 'A' ? 'Yes' : 'No');
        $title[] = $this->__('Leaf') . ': ' . ($node['is_leaf'] ? 'Yes' : 'No');
        $title[] = $this->__('Locked') . ': ' . ($node['is_locked'] ? 'Yes' : 'No');

        return implode('<br />', $title);
    }
}

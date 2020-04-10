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

namespace Zikula\CategoriesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\Repository\CategoryRepository;
use Zikula\CategoriesModule\Form\Type\CategoryTreeType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Controller for handling categories.
 *
 * @Route("/admin/category")
 */
class CategoryController extends AbstractController
{
    private $domTreeNodePrefix = 'node_';

    /**
     * @Route("/list/{id}", requirements={"category" = "^[1-9]\d*$"}, defaults={"id" = 1})
     * @Theme("admin")
     * @Template("@ZikulaCategoriesModule/Category/list.html.twig")
     *
     * @see https://jstree.com/
     * @see https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/tree.md
     * @throws AccessDeniedException Thrown if the user doesn't have edit permission for the module
     */
    public function listAction(
        Request $request,
        CategoryEntity $category,
        CategoryRepository $categoryRepository
    ): array {
        if (!$this->hasPermission('ZikulaCategoriesModule::category', '::', ACCESS_EDIT)
            || !$this->hasPermission('ZikulaCategoriesModule::category', 'ID::' . $category->getId(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $categoryRepository->recover();
        $this->getDoctrine()->getManager()->flush();
        $tree = $categoryRepository->childrenHierarchy(
            $category, /* node to start from */
            false, /* false: load all children, true: only direct */
            $this->getNodeOptions($request)
        );
        $form = $this->createFormBuilder()
            ->add('category', CategoryTreeType::class, [
                'label' => $this->trans('New parent'),
                'includeLeaf' => false,
            ])->getForm();

        return [
            'category' => $category,
            'tree' => $tree,
            'categorySelector' => $form->createView()
        ];
    }

    private function getNodeOptions(Request $request): array
    {
        $locale = $request->getLocale();

        return [
            'decorate' => true,
            'html' => true,
            'childOpen' => function($node) {
                $jsTreeData = [];
                $jsTreeData['disabled'] = 'A' !== $node['status'];
                $jsTreeData['type'] = $node['leaf'] ? 'leaf' : 'default';
                $jsTreeData = 'data-jstree="' . htmlentities(json_encode($jsTreeData)) . '" ';

                return '<li ' . $jsTreeData . 'class="jstree-open" id="' . $this->domTreeNodePrefix . $node['id'] . '">';
            },
            'nodeDecorator' => function($node) use ($locale) {
                $displayName = $node['displayName'][$locale] ?? $node['name'];
                $title = ' title="' . $this->createTitleAttribute($node, $displayName, $locale) . '"';
                $classes = [];
                if ($node['locked']) {
                    $classes[] = 'locked';
                }
                if ($node['leaf']) {
                    $classes[] = 'leaf';
                }
                $class = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';

                return '<a' . $class . $title . ' href="#">' . $displayName . '</a>';
            }
        ];
    }

    private function createTitleAttribute(array $node, string $displayName, string $locale): string
    {
        $title = [];
        $title[] = $this->trans('ID') . ': ' . $node['id'];
        $title[] = $this->trans('Name') . ': ' . $node['name'];
        $title[] = $this->trans('Display name') . ': ' . $displayName;
        $title[] = $this->trans('Description') . ': ' . ($node['displayDesc'][$locale] ?? '');
        $title[] = $this->trans('Value') . ': ' . $node['value'];
        $title[] = $this->trans('Active') . ': ' . ('A' === $node['status'] ? 'Yes' : 'No');
        $title[] = $this->trans('Leaf') . ': ' . ($node['leaf'] ? 'Yes' : 'No');
        $title[] = $this->trans('Locked') . ': ' . ($node['locked'] ? 'Yes' : 'No');

        return implode('<br />', $title);
    }
}

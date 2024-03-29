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

namespace Zikula\CategoriesBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\CategoriesBundle\Entity\Category;
use Zikula\CategoriesBundle\Form\Type\CategoryTreeType;
use Zikula\CategoriesBundle\Repository\CategoryRepository;

#[Route('/categories/admin/category')]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    private string $domTreeNodePrefix = 'node_';

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @see https://jstree.com/
     * @see https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/tree.md
     */
    #[Route('/list/{id}', name: 'zikulacategoriesbundle_category_listcategories', requirements: ['category' => "^[1-9]\d*$"], defaults: ['id' => 1])]
    public function listCategories(
        Request $request,
        ManagerRegistry $doctrine,
        Category $category,
        CategoryRepository $categoryRepository
    ): Response {
        $categoryRepository->recover();
        $doctrine->getManager()->flush();
        $tree = $categoryRepository->childrenHierarchy(
            $category, /* node to start from */
            false, /* false: load all children, true: only direct */
            $this->getNodeOptions($request)
        );
        $form = $this->createFormBuilder()
            ->add('category', CategoryTreeType::class, [
                'label' => $this->translator->trans('New parent'),
                'includeLeaf' => false,
            ])->getForm();

        return $this->render('@ZikulaCategories/Category/list.html.twig', [
            'category' => $category,
            'tree' => $tree,
            'categorySelector' => $form,
        ]);
    }

    private function getNodeOptions(Request $request): array
    {
        $locale = $request->getLocale();

        return [
            'decorate' => true,
            'html' => true,
            'childOpen' => function ($node) {
                $jsTreeData = [];
                $jsTreeData['disabled'] = 'A' !== $node['status'];
                $jsTreeData['type'] = $node['leaf'] ? 'leaf' : 'default';
                $jsTreeData = 'data-jstree="' . htmlentities(json_encode($jsTreeData)) . '" ';

                return '<li ' . $jsTreeData . 'class="jstree-open" id="' . $this->domTreeNodePrefix . $node['id'] . '">';
            },
            'nodeDecorator' => function ($node) use ($locale) {
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

                return '<a' . $class . $title . ' href="#">' . htmlspecialchars($displayName) . '</a>';
            }
        ];
    }

    private function createTitleAttribute(array $node, string $displayName, string $locale): string
    {
        $title = [];
        $title[] = $this->translator->trans('ID') . ': ' . $node['id'];
        $title[] = $this->translator->trans('Name') . ': ' . $node['name'];
        $title[] = $this->translator->trans('Display name') . ': ' . $displayName;
        $title[] = $this->translator->trans('Description') . ': ' . ($node['displayDesc'][$locale] ?? '');
        $title[] = $this->translator->trans('Value') . ': ' . $node['value'];
        $title[] = $this->translator->trans('Active') . ': ' . ('A' === $node['status'] ? 'Yes' : 'No');
        $title[] = $this->translator->trans('Leaf') . ': ' . ($node['leaf'] ? 'Yes' : 'No');
        $title[] = $this->translator->trans('Locked') . ': ' . ($node['locked'] ? 'Yes' : 'No');

        return htmlspecialchars(implode('<br />', $title));
    }
}

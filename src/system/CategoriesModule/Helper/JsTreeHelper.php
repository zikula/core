<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Helper;

use DataUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula_Tree;

/**
 * JS Tree helper functions for the categories module.
 */
class JsTreeHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * JsTreeHelper constructor.
     *
     * @param TranslatorInterface $translator   TranslatorInterface service instance
     * @param RouterInterface     $router       RouterInterface service instance
     * @param RequestStack        $requestStack RequestStack service instance
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * Get the java-script for the tree menu.
     *
     * @param array   $cats             The categories array to represent in the tree
     * @param boolean $doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true)
     * @param boolean $sortable         Sets the zikula tree option sortable (optional) (default=false)
     * @param array   $options          Options array for Zikula_Tree
     *
     * @return string generated tree JS text
     */
    public function getCategoryTreeJs($cats, $doReplaceRootCat = true, $sortable = false, array $options = [])
    {
        $leafNodes = [];
        foreach ($cats as $i => $c) {
            if ($doReplaceRootCat && $c['id'] == 1 && $c['name'] == '__SYSTEM__') {
                $c['name'] = $this->translator->__('Root category');
            }
            $cats[$i] = $this->getCategoryTreeJsNode($c);
            if ($c['is_leaf']) {
                $leafNodes[] = $c['id'];
            }
        }

        $tree = new Zikula_Tree();
        $tree->setOption('id', 'categoriesTree');
        $tree->setOption('sortable', $sortable);
        // disable drag and drop for root category
        $tree->setOption('disabled', [1]);
        $tree->setOption('disabledForDrop', $leafNodes);
        if (!empty($options)) {
            $tree->setOptionArray($options);
        }
        $tree->loadArrayData($cats);

        return $tree->getHTML();
    }

    /**
     * Get the java-script for the tree menu using jQuery.
     *
     * @param array   $cats             The categories array to represent in the tree
     * @param boolean $doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true)
     * @param boolean $sortable         Sets the zikula tree option sortable (optional) (default=false)
     * @param array   $options          Options array for Zikula_Tree
     *
     * @return string generated tree JS text
     */
    public function getCategoryTreeJqueryJs($cats, $doReplaceRootCat = true, $sortable = false, array $options = [])
    {
        $leafNodes = [];
        foreach ($cats as $i => $c) {
            if ($doReplaceRootCat && $c['id'] == 1 && $c['name'] == '__SYSTEM__') {
                $c['name'] = $this->translator->__('Root category');
            }
            $cats[$i] = $this->getCategoryTreeJsNode($c);
            if ($c['is_leaf']) {
                $leafNodes[] = $c['id'];
            }
        }

        $tree = new Zikula_Tree();
        $tree->setOption('id', 'categoriesTree');
        $tree->setOption('sortable', $sortable);
        // disable drag and drop for root category
        $tree->setOption('disabled', [1]);
        $tree->setOption('disabledForDrop', $leafNodes);
        if (!empty($options)) {
            $tree->setOptionArray($options);
        }
        $tree->loadArrayData($cats);

        return $tree->getJqueryHtml();
    }

    /**
     * create a JSON formatted object compatible with jsTree node structure for one category (includes children)
     *
     * @param CategoryEntity $category
     * @return array
     */
    public function getJsTreeNodeFromCategory(CategoryEntity $category)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();

        return [
            'id' => 'node_' . $category->getId(),
            'text' => $category->getDisplay_name($lang),
            'icon' => $category->getIs_leaf() ? false : 'fa fa-folder',
            'state' => [
                'open' => false,
                'disabled' => false,
                'selected' => false
            ],
            'children' => $this->getJsTreeNodeFromCategoryArray($category->getChildren()),
            'li_attr' => [
                'class' => $category->getStatus() == 'I' ? 'z-tree-unactive' : ''
            ],
            'a_attr' => [
                'title' => $this->createTitleAttribute($category->toArray(), $category->getDisplay_name($lang), $lang)
            ]
        ];
    }

    /**
     * create a JSON formatted object compatible with jsTree node structure from an array of categories
     *
     * @param $categories
     * @return array
     */
    public function getJsTreeNodeFromCategoryArray($categories)
    {
        $result = [];
        foreach ($categories as $category) {
            $result[] = $this->getJsTreeNodeFromCategory($category);
        }

        return $result;
    }

    /**
     * Prepare category for the tree menu.
     *
     * @param array $category Category data
     *
     * @return array Prepared category data
     */
    public function getCategoryTreeJsNode($category)
    {
        $request = $this->requestStack->getCurrentRequest();
        $lang = $request->getLocale();

        $url = $this->router->generate('zikulacategoriesmodule_admin_edit', [
            'mode' => 'edit',
            'cid' => $category['id']
        ]);

        if ($request->attributes->get('_zkType') == 'admin') {
            $url .= '#top';
        }

        if (isset($category['display_name'][$lang]) && !empty($category['display_name'][$lang])) {
            $name = DataUtil::formatForDisplay($category['display_name'][$lang]);
            $displayName = $name;
        } else {
            $name = DataUtil::formatForDisplay($category['name']);
            $displayName = '';
        }
        $category['name'] = $name;
        $category['active'] = $category['status'] == 'A' ? true : false;
        $category['href'] = $url;
        $category['title'] = $this->createTitleAttribute($category, $displayName, $lang);
        $category['class'] = [];
        if ($category['is_locked']) {
            $category['class'][] = 'locked';
        }
        if ($category['is_leaf']) {
            $category['class'][] = 'leaf';
        } else {
            $category['class'][] = 'z-tree-fixedparent';
        }
        if (!$category['active']) {
            $category['class'][] = 'z-tree-unactive';
        }
        $category['class'] = implode(' ', $category['class']);

        if (!$category['is_leaf']) {
            $category['icon'] = 'folder_open.png';
        }

        return $category;
    }

    /**
     * create and format a string suitable for use as title attribute in anchor tag
     *
     * @param $category
     * @param $displayName
     * @param $lang
     * @return string
     */
    private function createTitleAttribute($category, $displayName, $lang)
    {
        $title = [];
        $title[] = $this->translator->__('ID') . ': ' . $category['id'];
        $title[] = $this->translator->__('Name') . ': ' . DataUtil::formatForDisplay($category['name']);
        $title[] = $this->translator->__('Display name') . ': ' . $displayName;
        $title[] = $this->translator->__('Description') . ': ' . (isset($category['display_desc'][$lang]) ? DataUtil::formatForDisplay($category['display_desc'][$lang]) : '');
        $title[] = $this->translator->__('Value') . ': ' . $category['value'];
        $title[] = $this->translator->__('Active') . ': ' . ($category['status'] == 'A' ? 'Yes' : 'No');
        $title[] = $this->translator->__('Leaf') . ': ' . ($category['is_leaf'] ? 'Yes' : 'No');
        $title[] = $this->translator->__('Locked') . ': ' . ($category['is_locked'] ? 'Yes' : 'No');

        return implode('<br />', $title);
    }
}

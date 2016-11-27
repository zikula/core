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

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Category sorting helper functions for the categories module.
 */
class CategorySortingHelper
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * CategorySortingHelper constructor.
     *
     * @param RequestStack $requestStack RequestStack service instance
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Compare function for ML name field.
     *
     * @param array $catA First category
     * @param array $catB Second category
     *
     * @return The resulting compare value
     */
    public function compareName($catA, $catB)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();

        if (!$catA['display_name'][$lang]) {
            $catA['display_name'][$lang] = $catA['name'];
        }

        if ($catA['display_name'][$lang] == $catB['display_name'][$lang]) {
            return 0;
        }

        return strcmp($catA['display_name'][$lang], $catB['display_name'][$lang]);
    }

    /**
     * Compare function for ML description field
     *
     * @param array $catA First category
     * @param array $catB Second category
     *
     * @return The resulting compare value
     */
    public function compareDesc($catA, $catB)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();

        if ($catA['display_desc'][$lang] == $catB['display_desc'][$lang]) {
            return 0;
        }

        return strcmp($catA['display_desc'][$lang], $catB['display_desc'][$lang]);
    }

    /**
     * Utility function to sort a category array by the current locale of   either the ML name or description.
     *
     *  The resulting sorted category array $cats updated by reference nothing is returned.
     *
     * @param array  &$cats The categories array
     * @param string $func Which compare function to use (determines field to be used for comparison) (optional) (default='compareName')
     *
     * @return void
     */
    public function sortByLocale(&$cats, $func = 'compareName')
    {
        usort($cats, [$this, $func]);

        return;
    }

    /**
     * Resequence the sort fields for the given category.
     *
     * @param array   $cats The categories array
     * @param integer $step The counting step/interval (optional) (default=1)
     *
     * @return true if something was done, false if an emtpy $cats was passed in
     */
    public function resequence($cats, $step = 1)
    {
        if (!$cats) {
            return false;
        }

        $c = 0;
        $ak = array_keys($cats);
        foreach ($ak as $k) {
            $cats[$k]['sort_value'] = ++$c * $step;
        }

        return $cats;
    }

    /**
     * insert one leaf in a category tree (path as keys) recursively.
     *
     * Example:
     * $tree[name] = array of children
     * $tree[name]['_/_'] = branch/leaf data.
     *
     * @param array  &$tree       Tree or branch
     * @param array  $entry       The entry to insert
     * @param string $currentpath The current path to use (optional) (default=$entry['ipath'])
     *
     * @return array Tree
     */
    public function insertTreeLeaf(&$tree, $entry, $currentpath = null)
    {
        if ($currentpath === null) {
            $currentpath = $entry['ipath'];
        }
        $currentpath = trim($currentpath, '/ ');
        $pathlist = explode('/', $currentpath);
        $root = $pathlist[0];
        if (!array_key_exists($root, $tree)) {
            $tree[$root] = [];
        }
        if (count($pathlist) == 1) {
            $tree[$root]['_/_'] = $entry;

            return $tree;
        } else {
            unset($pathlist[0]);
            $this->insertTreeLeaf($tree[$root], $entry, implode('/', $pathlist));
        }
    }

    /**
     * make a list, sorted on each level, from a tree.
     *
     * @param array $tree Nested array from insertTreeLeaf
     * @param array &$cats List of categories (initially empty array)
     *
     * @return void
     */
    public function sortTree($tree, &$cats)
    {
        global $_catSortField;
        $sorted = [];
        foreach ($tree as $k => $v) {
            if ($k == '_/_') {
                $cats[] = $v;
            } else {
                if (isset($v['_/_'][$_catSortField])) {
                    if ($v['_/_'][$_catSortField] > 0 && $v['_/_'][$_catSortField] < 2147483647) {
                        $sorted[$k] = $v['_/_'][$_catSortField];
                    } else {
                        $sorted[$k] = $v['_/_']['name'];
                    }
                } else {
                    $sorted[$k] = null;
                }
            }
        }

        uasort($sorted, [$this, 'treeSortCmp']);

        foreach ($sorted as $k => $v) {
            $this->sortTree($tree[$k], $cats);
        }
    }

    /**
     * Internal callback function for int/string comparation.
     *
     * It is supposed to compate integer items numerically and string items as strings,
     * so integers will be before strings (unlike SORT_REGULAR flag for array sort functions).
     *
     * @param string $a The first value
     * @param string $b The second value
     *
     * @return int 0 if $a and $b are equal, 1 ir $a is greater then $b, -1 if $a is less than $b
     */
    private function treeSortCmp($a, $b)
    {
        if ($a === $b) {
            return 0;
        }
        if (!is_numeric($a) || !is_numeric($b)) {
            return strcmp($a, $b);
        }

        return ($a < $b) ? -1 : 1;
    }

    /**
     * Take a raw list of category data, return it sorted on each level.
     *
     * @param array  $cats      List of categories (arrays)
     * @param string $sortField The sort field (optional)
     * @param string $assocKey  Key of category arrays (optional)
     *
     * @return array list of categories, sorted on each level
     */
    public function sortCategories($cats, $sortField = '', $assocKey = '')
    {
        if (!$cats) {
            return $cats;
        }

        global $_catSortField;
        if ($sortField) {
            $_catSortField = $sortField;
        } else {
            $sortField = $_catSortField;
        }

        $tree = [];
        foreach ($cats as $c) {
            $this->insertTreeLeaf($tree, $c);
        }
        $new_cats = [];
        $this->sortTree($tree[1], $new_cats);

        if ($assocKey) {
            $new_cats_assoc = [];
            foreach ($new_cats as $c) {
                if (isset($c[$assocKey])) {
                    $new_cats_assoc[$c[$assocKey]] = $c;
                }
            }
            $new_cats = $new_cats_assoc;
        }

        return $new_cats;
    }
}

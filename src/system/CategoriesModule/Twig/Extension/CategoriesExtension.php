<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Twig\Extension;

use CategoryUtil;
use DataUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CategoriesExtension extends \Twig_Extension
{
    /**
     * @var Request
     */
    private $request;

    /**
     * CategoriesExtension constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('categoryPath', [$this, 'categoryPath'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('zikulacategoriesmodule_moduleTableSelector', [$this, 'moduleTableSelector'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('zikulacategoriesmodule_categorySelector', [$this, 'categorySelector'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Retrieves and displays the value of a category field (by default, the category's path).
     *
     * Examples:
     *
     * Get the path of category #1 and assign it to the template variable $category:
     * <samp>{% set category = categoryPath('1') %}</samp>
     *
     * Get the path of the category with an ipath of '/1/3/28/30' and display it.
     * <samp>{{ categoryPath('/1/3/28/30', 'ipath', 'path') }}</samp>
     *
     * Get the parent_id of the category with a path of
     * '/__SYSTEM__/General/ActiveStatus/Active' and assign it to the template
     * variable $parentid. Then use that template variable to retrieve and display
     * the parent's path.
     * <samp>{% set parentid = categoryPath('/__SYSTEM__/General/ActiveStatus/Active', 'parent_id') %}</samp>
     * <samp>{{ categoryPath(parentid) }}</samp>
     *
     * Example from a Content module template: get the sort value of the current
     * page's category and assign it to the template variable $catsortvalue:
     * <samp>{% set catSortValue = categoryPath(page.categoryId, 'sort_value') %}</samp>
     *
     * @param int|string $id       The category identifier
     * @param string     $idColumn Field name used for identification
     * @param string     $field    Desired output field
     * @param bool       $html     If set, return HTML (optional, default: false)
     *
     * @return string
     */
    public function categoryPath($id, $idColumn = '', $field = 'path', $html = false)
    {
        if ($idColumn == '') {
            $idColumn = is_numeric($id) ? 'id' : 'path';
        }

        if (!$id) {
            //$view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['category_path', 'id']));
            return '';
        }

        if (!$idColumn) {
            //$view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['category_path', 'idcolumn']));
            return '';
        }
        if (!in_array($idColumn, ['id', 'path', 'ipath'])) {
            //$view->trigger_error(__f('Error! in %1$s: invalid value for the %2$s parameter (%3$s).', ['category_path', 'idcolumn', $idColumn]));
            return '';
        }

        if (!$field) {
            //$view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['category_path', 'field']));
            return '';
        }

        $result = null;
        if ($idColumn == 'id') {
            $cat = CategoryUtil::getCategoryByID($id);
        } elseif (($idColumn == 'path') || ($idColumn == 'ipath')) {
            $cat = CategoryUtil::getCategoryByPath($id, $idColumn);
        }

        if ($cat) {
            if (isset($cat[$field])) {
                $result = $cat[$field];
            } else {
                //$view->trigger_error(__f('Error! Category [%1$s] does not have the field [%2$s] set.', [$id, $field]));
                return '';
            }
        } else {
            //$view->trigger_error(__f('Error! Cannot retrieve category with ID %s.', DataUtil::formatForDisplay($id)));
            return '';
        }

        if (isset($html) && is_bool($html) && $html) {
            return DataUtil::formatForDisplayHTML($result);
        }

        return DataUtil::formatForDisplay($result);
    }

    /**
     * Generates a module table selector.
     * @todo Temporary solution, to be removed after migration to Symfony Forms has been completed
     *
     * @param string     $modname       The module name
     * @param string     $name          The form field name
     * @param string|int $selectedValue The selected value
     * @param string|int $defaultValue  The default option's value
     * @param string     $defaultText   The default option's text
     *
     * @return string
     */
    public function moduleTableSelector($modname = null, $name = null, $selectedValue = 0, $defaultValue = 0, $defaultText = '')
    {
        return \HtmlUtil::getSelector_ModuleTables($modname, $name, $selectedValue, $defaultValue, $defaultText);
    }

    /**
     * Generates a category selector.
     * @todo Temporary solution, to be removed after migration to Symfony Forms has been completed
     *
     * @param int        $category      The parent category id
     * @param string     $field         The category field to use
     * @param string     $name          The form field name
     * @param string|int $selectedValue The selected value
     * @param string|int $defaultValue  The default option's value
     * @param string     $defaultText   The default option's text
     * @param bool       $recurse       Whether recurse into sub levels or not
     * @param bool       $relative      Whether to use relative pathes or not
     * @param bool       $includeRoot   Whether to include root category or not
     * @param bool       $includeLeaf   Whether to include leaf categories or not
     *
     * @return string
     */
    public function categorySelector($category = 0, $field = 'id', $name = null, $selectedValue = 0, $defaultValue = 0, $defaultText = '',
        $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true)
    {
        $lang = !empty($this->request) ? $this->request->getLocale() : 'en';

        $category = CategoryUtil::getCategoryByID($category);

        $categories = CategoryUtil::getSubCategoriesForCategory($category, $recurse, $relative, $includeRoot, $includeLeaf, false, '', '', null, 'sort_value');

        return CategoryUtil::getSelector_Categories($categories, $field, $selectedValue, $name, $defaultValue, $defaultText, 0, '', false, false, null, 1, null, '', $lang);
    }

    public function getName()
    {
        return 'zikulacategoriesmodule.twigextension';
    }
}

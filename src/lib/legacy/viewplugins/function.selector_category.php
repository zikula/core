<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Category selector.
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string
 */
function smarty_function_selector_category($params, Zikula_View $view)
{
    $categoryRegistryModule   = isset($params['categoryRegistryModule']) ? $params['categoryRegistryModule'] : '';
    $categoryRegistryTable    = isset($params['categoryRegistryTable']) ? $params['categoryRegistryTable'] : '';
    $categoryRegistryProperty = isset($params['categoryRegistryProperty']) ? $params['categoryRegistryProperty'] : '';

    $category         = isset($params['category']) ? $params['category'] : 0;
    $path             = isset($params['path']) ? $params['path'] : '';
    $pathfield        = isset($params['pathfield']) ? $params['pathfield'] : 'path';
    $field            = isset($params['field']) ? $params['field'] : 'id';
    $fieldIsAttribute = isset($params['fieldIsAttribute']) ? $params['fieldIsAttribute'] : null;
    $selectedValue    = isset($params['selectedValue']) ? $params['selectedValue'] : 0;
    $defaultValue     = isset($params['defaultValue']) ? $params['defaultValue'] : 0;
    $defaultText      = isset($params['defaultText']) ? $params['defaultText'] : '';
    $allValue         = isset($params['allValue']) ? $params['allValue'] : 0;
    $allText          = isset($params['allText']) ? $params['allText'] : '';
    $name             = isset($params['name']) ? $params['name'] : 'defaultselectorname';
    $submit           = isset($params['submit']) ? $params['submit'] : false;
    $recurse          = isset($params['recurse']) ? $params['recurse'] : true;
    $relative         = isset($params['relative']) ? $params['relative'] : true;
    $includeRoot      = isset($params['includeRoot']) ? $params['includeRoot'] : false;
    $includeLeaf      = isset($params['includeLeaf']) ? $params['includeLeaf'] : true;
    $all              = isset($params['all']) ? $params['all'] : false;
    $displayPath      = isset($params['displayPath']) ? $params['displayPath'] : false;
    $attributes       = isset($params['attributes']) ? $params['attributes'] : null;
    $assign           = isset($params['assign']) ? $params['assign'] : null;
    $editLink         = isset($params['editLink']) ? $params['editLink'] : true;
    $multipleSize     = isset($params['multipleSize']) ? $params['multipleSize'] : 1;
    $sortField        = isset($params['sortField']) ? $params['sortField'] : 'sort_value';
    $doReplaceRootCat = isset($params['doReplaceRootCat']) ? $params['doReplaceRootCat'] : null;
    $cssClass         = isset($params['cssClass']) ? $params['cssClass'] : '';

    if (isset($params['lang'])) {
        $lang = $params['lang'];
        $oldLocale = ZLanguage::getLocale();
        ZLanguage::setLocale($lang);
    } else {
        $lang = ZLanguage::getLanguageCode();
    }

    if (!$category && !$path && $categoryRegistryModule && $categoryRegistryTable && $categoryRegistryProperty) {
        $category = CategoryRegistryUtil::getRegisteredModuleCategory($categoryRegistryModule, $categoryRegistryTable, $categoryRegistryProperty);
    }

    // if we don't have a category-id we see if we can get a category by path
    if (!$category && $path) {
        $category = CategoryUtil::getCategoryByPath($path, $pathfield);
    } elseif (is_numeric($category)) {
        // check if we have a numeric category
        $category = CategoryUtil::getCategoryByID($category);
    } elseif (is_string($category) && strpos($category, '/') === 0) {
        // check if we have a string/path category
        $category = CategoryUtil::getCategoryByPath($category, $pathfield);
    }

    static $catCache;
    if (!$catCache) {
        $catCache = [];
    }

    $recurse = false;

    $cacheKey = "$category[id]||$recurse|$relative|$includeRoot|$includeLeaf|$all|||$attributes|$sortField";
    if (!isset($catCache[$cacheKey])) {
        $catCache[$cacheKey] = CategoryUtil::getSubCategoriesForCategory($category, $recurse, $relative, $includeRoot,
                                                                          $includeLeaf, $all, '', '', $attributes, $sortField);
    }

    $html = CategoryUtil::getSelector_Categories($catCache[$cacheKey], $field, $selectedValue, $name, $defaultValue, $defaultText,
                                                  $allValue, $allText, $submit, $displayPath, $doReplaceRootCat, $multipleSize, $fieldIsAttribute, $cssClass, $lang
                                                  );

/*
    if ($editLink && !empty($category) && SecurityUtil::checkPermission('ZikulaCategoriesModule::', "$category[id]::", ACCESS_EDIT)) {
        $url = DataUtil::formatForDisplay(ModUtil::url('ZikulaCategoriesModule', 'user', 'edit', ['dr' => $category['id']]));
        $html .= "&nbsp;&nbsp;<a href=\"$url\"><img src=\"".System::getBaseUrl()."images/icons/extrasmall/xedit.png\" title=\"" . __('Edit sub-category') . '" alt="' . __('Edit sub-category') . '" /></a>';
    }
*/
    if (isset($params['lang'])) {
        // Reset language again.
        ZLanguage::setLocale($oldLocale);
    }

    if ($assign) {
        $view->assign($assign, $html);
    } else {
        return $html;
    }
}

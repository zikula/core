<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

function smarty_function_selector_category ($params, &$smarty)
{
    $categoryRegistryModule   = isset($params['categoryRegistryModule'])   ? $params['categoryRegistryModule']   : '';
    $categoryRegistryTable    = isset($params['categoryRegistryTable'])    ? $params['categoryRegistryTable']    : '';
    $categoryRegistryProperty = isset($params['categoryRegistryProperty']) ? $params['categoryRegistryProperty'] : '';

    $category         = isset($params['category'])         ? $params['category']         : 0;
    $path             = isset($params['path'])             ? $params['path']             : '';
    $pathfield        = isset($params['pathfield'])        ? $params['pathfield']        : 'path';
    $field            = isset($params['field'])            ? $params['field']            : 'id';
    $fieldIsAttribute = isset($params['fieldIsAttribute']) ? $params['fieldIsAttribute'] : null;
    $selectedValue    = isset($params['selectedValue'])    ? $params['selectedValue']    : 0;
    $defaultValue     = isset($params['defaultValue'])     ? $params['defaultValue']     : 0;
    $defaultText      = isset($params['defaultText'])      ? $params['defaultText']      : '';
    $allValue         = isset($params['allValue'])         ? $params['allValue']         : 0;
    $allText          = isset($params['allText'])          ? $params['allText']          : '';
    $lang             = isset($params['lang'])             ? $params['lang']             : ZLanguage::getLanguageCode();
    $name             = isset($params['name'])             ? $params['name']             : 'defautlselectorname';
    $submit           = isset($params['submit'])           ? $params['submit']           : false;
    $recurse          = isset($params['recurse'])          ? $params['recurse']          : true;
    $relative         = isset($params['relative'])         ? $params['relative']         : true;
    $includeRoot      = isset($params['includeRoot'])      ? $params['includeRoot']      : false;
    $includeLeaf      = isset($params['includeLeaf'])      ? $params['includeLeaf']      : true;
    $all              = isset($params['all'])              ? $params['all']              : false;
    $displayPath      = isset($params['displayPath'])      ? $params['displayPath']      : false;
    $attributes       = isset($params['attributes'])       ? $params['attributes']       : null;
    $assign           = isset($params['assign'])           ? $params['assign']           : null;
    $editLink         = isset($params['editLink'])         ? $params['editLink']         : true;
    $multipleSize     = isset($params['multipleSize'])     ? $params['multipleSize']     : 1;
    $sortField        = isset($params['sortField'])        ? $params['sortField']        : 'sort_value';
    $doReplaceRootCat = isset($params['doReplaceRootCat']) ? $params['doReplaceRootCat'] : null;

    // disable attribution if we don't need it
    $_pnTables = null;
    if (!$fieldIsAttribute) {
        $t = $_pnTables = $GLOBALS['pntables'];
        $t['categories_category_db_extra_enable_attribution'] = false;
        $GLOBALS['pntables'] = $t;
    }

    if (!$category && !$path && $categoryRegistryModule && $categoryRegistryTable && $categoryRegistryProperty) {
        $category = CategoryRegistryUtil::getRegisteredModuleCategory ($categoryRegistryModule, $categoryRegistryTable, $categoryRegistryProperty);
    }

    $allCats = array();
    // if we don't have a category-id we see if we can get a category by path
    if (!$category && $path) {
        $category = CategoryUtil::getCategoryByPath ($path, $pathfield);

    // check if we have a numeric category
    } elseif (is_numeric($category)) {
        $category = CategoryUtil::getCategoryByID ($category);

    // check if we have a string/path category
    } elseif (is_string($category) && strpos($category, '/')===0) {
        $category = CategoryUtil::getCategoryByPath ($category, $pathfield);
    }

    static $catCache;
    if (!$catCache) {
        $catCache = array();
    }
    $cacheKey = "$category[id]||$recurse|$relative|$includeRoot|$includeLeaf|$all|||$attributes|$sortField";
    if (!isset($catCache[$cacheKey])) {
        $catCache[$cacheKey] = CategoryUtil::getSubCategoriesForCategory ($category, $recurse, $relative, $includeRoot,
                                                                          $includeLeaf, $all, '', '', $attributes, $sortField);
    }

    $html = CategoryUtil::getSelector_Categories ($catCache[$cacheKey], $field, $selectedValue, $name, $defaultValue, $defaultText,
                                                  $allValue, $allText, $submit, $displayPath, $doReplaceRootCat, $multipleSize, $fieldIsAttribute);

    if ($editLink && !empty($category) && SecurityUtil::checkPermission( 'Categories::', "$category[id]::", ACCESS_EDIT)) {
        $url = DataUtil::formatForDisplay(pnModURL ('Categories', 'user', 'edit', array('dr' => $category['id'])));
        $html .= "&nbsp;&nbsp;<a href=\"$url\"><img src=\"".System::getBaseUrl()."images/icons/extrasmall/xedit.gif\" title=\"" . __('Edit sub-category') . '" alt="' . __('Edit sub-category') . '" /></a>';
    }

    // re-enable attribution if we disabled it previously
    if ($_pnTables) {
        $GLOBALS['pntables'] = $_pnTables;
    }

    if ($assign) {
        $smarty->assign($assign, $html);
    } else {
        return $html;
    }
}

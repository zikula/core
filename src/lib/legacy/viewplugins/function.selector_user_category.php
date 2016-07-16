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
 * User category selector.
 *
 * Available parameters:
 *   - btnText:  If set, the results are assigned to the corresponding variable instead of printed out
 *   - cid:      category ID
 *
 * Example
 * {selector_user_category cid="1" assign="category"}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string HTML code of the selector
 */

function smarty_function_selector_user_category($params, Zikula_View $view)
{
    $field            = isset($params['field'])            ? $params['field']            : 'id';
    $selectedValue    = isset($params['selectedValue'])    ? $params['selectedValue']    : 0;
    $defaultValue     = isset($params['defaultValue'])     ? $params['defaultValue']     : 0;
    $defaultText      = isset($params['defaultText'])      ? $params['defaultText']      : '';
    $lang             = isset($params['lang'])             ? $params['lang']             : ZLanguage::getLanguageCode();
    $name             = isset($params['name'])             ? $params['name']             : 'defautlselectorname';
    $recurse          = isset($params['recurse'])          ? $params['recurse']          : true;
    $relative         = isset($params['relative'])         ? $params['relative']         : true;
    $includeRoot      = isset($params['includeRoot'])      ? $params['includeRoot']      : false;
    $includeLeaf      = isset($params['includeLeaf'])      ? $params['includeLeaf']      : true;
    $all              = isset($params['all'])              ? $params['all']              : false;
    $displayPath      = isset($params['displayPath'])      ? $params['displayPath']      : false;
    $attributes       = isset($params['attributes'])       ? $params['attributes']       : null;
    $assign           = isset($params['assign'])           ? $params['assign']           : null;
    $editLink         = isset($params['editLink'])         ? $params['editLink']         : true;
    $submit           = isset($params['submit'])           ? $params['submit']           : false;
    $multipleSize     = isset($params['multipleSize'])     ? $params['multipleSize']     : 1;
    $doReplaceRootCat = false;

    $userCats = ModUtil::apiFunc('ZikulaCategoriesModule', 'user', 'getusercategories', ['returnCategory' => 1, 'relative' => $relative]);
    $html = CategoryUtil::getSelector_Categories($userCats, $field, $selectedValue, $name, $defaultValue, $defaultText,
                                                  $submit, $displayPath, $doReplaceRootCat, $multipleSize);

    if ($editLink && $allowUserEdit && UserUtil::isLoggedIn() && SecurityUtil::checkPermission('ZikulaCategoriesModule::', "$category[id]::", ACCESS_EDIT)) {
        $url = ModUtil::url('ZikulaCategoriesModule', 'user', 'edituser');
        $html .= "&nbsp;&nbsp;<a href=\"$url\">" . __('Edit sub-categories') . '</a>';
    }

    if ($assign) {
        $view->assign($assign, $html);
    } else {
        return $html;
    }
}

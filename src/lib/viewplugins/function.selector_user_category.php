<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string HTML code of the selector.
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


    $userCats= ModUtil::apiFunc ('Categories', 'user', 'getusercategories', array('returnCategory'=>1, 'relative'=>$relative));
    $html = CategoryUtil::getSelector_Categories ($userCats, $field, $selectedValue, $name, $defaultValue, $defaultText,
                                                  $submit, $displayPath, $doReplaceRootCat, $multipleSize);

    if ($editLink && $allowUserEdit && UserUtil::isLoggedIn() && SecurityUtil::checkPermission( 'Categories::', "$category[id]::", ACCESS_EDIT)) {
        $url = ModUtil::url ('Categories', 'user', 'edituser');
        $html .= "&nbsp;&nbsp;<a href=\"$url\">" . __('Edit sub-categories') . '</a>';
    }

    if ($assign) {
        $view->assign($assign, $html);
    } else {
        return $html;
    }
}

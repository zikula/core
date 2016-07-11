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
 * Groups selector.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_selector_group($params, Zikula_View $view)
{
    $field            = isset($params['field'])            ? $params['field']            : 'uid';
    $selectedValue    = isset($params['selectedValue'])    ? $params['selectedValue']    : 0;
    $defaultValue     = isset($params['defaultValue'])     ? $params['defaultValue']     : 0;
    $defaultText      = isset($params['defaultText'])      ? $params['defaultText']      : '';
    $allValue         = isset($params['allValue'])         ? $params['allValue']         : 0;
    $allText          = isset($params['allText'])          ? $params['allText']          : '';
    $name             = isset($params['name'])             ? $params['name']             : 'defautlselectorname';
    $assign           = isset($params['assign'])           ? $params['assign']           : null;
    $submit           = isset($params['submit'])           ? $params['submit']           : false;
    $multipleSize     = isset($params['multipleSize'])     ? $params['multipleSize']     : 1;
    $disabled         = isset($params['disabled'])         ? $params['disabled']         : 0;

    $html = HtmlUtil::getSelector_Group($name, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, '', $submit, $disabled, $multipleSize);

    if ($assign) {
        $view->assign($assign, $html);
    } else {
        return $html;
    }
}

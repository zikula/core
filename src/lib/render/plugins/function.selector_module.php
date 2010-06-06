<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * selector_module: generate a PN Module selector
 *
 * @param    name           The name of the selector tag
 * @param    selectedValue  The currently selected value
 * @param    defaultValue   The default value (only used if no selectedValue is supplied)
 * @param    defaultText    Text to go with the default value
 * @param    allValue       Wether or not to include an 'All' selector
 * @param    allText        Text to go with the 'All' select value
 */
function smarty_function_selector_module ($params, &$smarty)
{
    $name          = isset($params['name'])          ? $params['name']          : 'defaultselectorname';
    $field         = isset($params['field'])         ? $params['field']         : 'name';
    $selectedValue = isset($params['selectedValue']) ? $params['selectedValue'] : 0;
    $defaultValue  = isset($params['defaultValue'])  ? $params['defaultValue']  : 0;
    $defaultText   = isset($params['defaultText'])   ? $params['defaultText']   : null;
    $allValue      = isset($params['allValue'])      ? $params['allValue']      : false;
    $allText       = isset($params['allText'])       ? $params['allText']       : null;
    $submit        = isset($params['submit'])        ? $params['submit']        : false;
    $disabled      = isset($params['disabled'])      ? $params['disabled']      : false;
    $multipleSize  = isset($params['multipleSize'])  ? $params['multipleSize']  : 1;

    return HtmlUtil::getSelector_PNModule ($name, $selectedValue, $defaultValue, $defaultText, $allValue, $allText,
                                           $submit, $disabled, $multipleSize, $field);
}

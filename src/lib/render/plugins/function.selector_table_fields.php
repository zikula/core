<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * selector_table_fields: generate a table-fields selector
 *
 * @param   name        The name of the selector tag
 * @param   selectedValue   The currently selected value
 * @param   defaultValue    The default value (only used if no selectedValue is supplied)
 * @param   defaultText Text to go with the default value
 * @param   includeAll  Wether or not to include an 'All' selector
 * @param   allText     Text to go with the 'All' select value
 */
function smarty_function_selector_table_fields ($params, &$smarty)
{
    $modname        = (isset($params['modname'])        ? $params['modname']        : '');
    $tablename      = (isset($params['tablename'])      ? $params['tablename']      : '');
    $name           = (isset($params['name'])           ? $params['name']           : '');
    $selectedValue  = (isset($params['selectedValue'])  ? $params['selectedValue']  : 0);
    $defaultValue   = (isset($params['defaultValue'])   ? $params['defaultValue']   : 0);
    $defaultText    = (isset($params['defaultText'])    ? $params['defaultText']    : '');
    $submit         = (isset($params['submit'])         ? $params['submit']         : false);

    return HtmlUtil::getSelector_TableFields ($modname, $tablename, $name, $selectedValue, $defaultValue, $defaultText, $submit);
}

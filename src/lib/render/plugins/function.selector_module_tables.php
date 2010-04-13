<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * selector_module_tables: generate a PN Module table selector
 *
 * @author  Robert Gasch
 * @param   modname        The module name we wish to get tables for
 * @param   name           The name of the selector tag
 * @param   selectedValue  The currently selected value
 * @param   defaultValue   The default value (only used if no selectedValue is supplied)
 * @param   defaultText    Text to go with the default value
 * @param   remove         Text to go with the default value
 * @param   nStripChars    Number of characters to remove (to remove pn database table prefix)
 * @param   submit         Wether or not to auto-submit after selection
 * @param   disabled       Wether or not disable the selector
 * @param   multipleSize   The size of the selector to create (default=1)
 *
 */
function smarty_function_selector_module_tables ($params, &$smarty)
{
    $modname       = isset($params['modname'])       ? $params['modname']        : null;
    $name          = isset($params['name'])          ? $params['name']           : null;
    $selectedValue = isset($params['selectedValue']) ? $params['selectedValue']  : 0;
    $defaultValue  = isset($params['defaultValue'])  ? $params['defaultValue']   : 0;
    $defaultText   = isset($params['defaultText'])   ? $params['defaultText']    : '';
    $remove        = isset($params['remove'])        ? $params['remove']         : false;
    $nStripChars   = isset($params['nStripChars'])   ? $params['nStripChars']    : 0;
    $submit        = isset($params['submit'])        ? $params['submit']         : false;
    $disabled      = isset($params['disabled'])      ? $params['disabled']       : false;
    $multipleSize  = isset($params['multipleSize'])  ? $params['multipleSize']   : 1;

    return HtmlUtil::getSelector_ModuleTables ($modname, $name, $selectedValue, $defaultValue, $defaultText,
                                               $submit, $remove, $disabled, $nStripChars, $multipleSize);
}

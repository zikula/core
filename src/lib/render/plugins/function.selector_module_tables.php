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
 * @param   name        The name of the selector tag
 * @param   selectedValue   The currently selected value
 * @param   defaultValue    The default value (only used if no selectedValue is supplied)
 * @param   defaultText Text to go with the default value
 * @param   includeAll  Wether or not to include an 'All' selector
 * @param   allText     Text to go with the 'All' select value
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
    $submit        = isset($params['submit'])         ? $params['submit']       : false;
    $disabled      = isset($params['disabled'])       ? $params['disabled']     : false;
    $multipleSize  = isset($params['multipleSize'])   ? $params['multipleSize'] : 1;

    return HtmlUtil::getSelector_ModuleTables ($modname, $name, $selectedValue, $defaultValue, $defaultText,
                                               $submit, $remove, $disabled, $nStripChars, $multipleSize);
}

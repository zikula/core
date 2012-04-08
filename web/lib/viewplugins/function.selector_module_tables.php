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
 * selector_module_tables: generate a PN Module table selector
 *
 * Parameter:
 *  modname       The module name we wish to get tables for
 *  name          The name of the selector tag
 *  selectedValue The currently selected value
 *  defaultValue  The default value (only used if no selectedValue is supplied)
 *  defaultText   Text to go with the default value
 *  remove        Text to go with the default value
 *  nStripChars   Number of characters to remove (to remove pn database table prefix)
 *  submit        Wether or not to auto-submit after selection
 *  disabled      Wether or not disable the selector
 *  multipleSize  The size of the selector to create (default=1)
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_selector_module_tables ($params, Zikula_View $view)
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

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
 * Zikula_View function to add a value to a multicontent page variable
 *
 * This function obtains a page-specific variable from the Zikula system.
 *
 * Available parameters:
 *   - name:     The name of the page variable to set
 *   - value:    The value of the page variable to set, comma separated list is possible
 *
 * Zikula doesn't impose any restriction on the page variabl's name except for duplicate
 * and reserved names. As of this writing, the list of reserved names consists of
 * <ul>
 * <li>title</li>
 * <li>stylesheet</li>
 * <li>javascript</li>
 * <li>body</li>
 * <li>rawtext</li>
 * <li>footer</li>
 * </ul>
 *
 * Examples
 *   {pageaddvar name='javascript' value='path/to/myscript.js'}
 *   {pageaddvar name='javascript' value='path/to/myscript.js,path/to/another/script.js'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_function_pageaddvar($params, Zikula_View $view)
{
    $name  = isset($params['name'])  ? $params['name']  : null;
    $value = isset($params['value']) ? $params['value'] : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pageaddvar', 'name')));
        return false;
    }

    if (!$value) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pageaddvar', 'value')));
        return false;
    }

    if ($name != 'rawtext') {
        $value = explode(',', $value);
    }
    PageUtil::addVar($name, $value);
}

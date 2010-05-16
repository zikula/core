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

/**
 * Smarty function to obtain form variable.
 *
 * This plugin obtains the variable from the input namespace. It removes any preparsing
 * done by PHP to ensure that the string is exactly as expected, without any escaped characters.
 * it also removes any HTML tags that could be considered dangerous to the Zikula system's security.
 *
 * Available parameters:
 *   - name: the name of the parameter
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param object &$smarty Reference to the Smarty object.
 *
 * @return       string      the variables content
 */
function smarty_function_varcleanfrominput($params, &$smarty)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $name   = isset($params['name'])   ? $params['name']   : null;

    if (!$name) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnvarcleanfrominput', 'name')));
        return false;
    }

    $result = FormUtil::getPassedValue($name);

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}

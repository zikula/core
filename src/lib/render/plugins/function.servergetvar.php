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
 * Smarty function to get module variable
 *
 * This function obtains a server-specific variable from the system.
 *
 * Note that the results should be handled by the DataUtil::formatForDisplay of the
 * DataUtil::formatForDisplayHTML modifiers before being displayed.
 *
 *
 * Available parameters:
 *   - name:     The name of the module variable to obtain
 *
 * Example
 *   <!--[servergetvar name='PHP_SELF']-->
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $assign      (optional) If set then result will be assigned to this template variable
 * @param        string      $default     (optional) The default value to return if the server variable is not set
 * @return       string      The module variable
 */
function smarty_function_servergetvar($params, &$smarty)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $default = isset($params['default']) ? $params['default'] : null;
    $name    = isset($params['name'])    ? $params['name']    : null;

    if (!$name) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('System::serverGetVar', 'name')));
        return false;
    }

    $result = System::serverGetVar($name, $default);

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return DataUtil::formatForDisplay($result);
    }
}

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
 * Zikula_View function to get module variable
 *
 * This function obtains a server-specific variable from the system.
 *
 * Note that the results should be handled by the safetext or the safehtml
 * modifier before being displayed.
 *
 *
 * Available parameters:
 *   - name:     The name of the module variable to obtain
 *   - assign:   (optional) If set then result will be assigned to this template variable
 *   - default:  (optional) The default value to return if the server variable is not set
 *
 * Example
 *   {servergetvar name='PHP_SELF'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The module variable.
 */
function smarty_function_servergetvar($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $default = isset($params['default']) ? $params['default'] : null;
    $name    = isset($params['name'])    ? $params['name']    : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('System::serverGetVar', 'name')));

        return false;
    }

    $result = System::serverGetVar($name, $default);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return DataUtil::formatForDisplay($result);
    }
}

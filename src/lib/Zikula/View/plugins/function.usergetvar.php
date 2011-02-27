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
 * Zikula_View function to get a user variable
 *
 * This function obtains a user-specific variable from the Zikula system.
 *
 * Note that the results should be handled by the safetext or the safehtml
 * modifier before being displayed.
 *
 *
 * Available parameters:
 *   - name:    The name of the variable being requested
 *   - uid:     The user id to obtain the variable for - this parameter is optional
 *   - assign:  If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {usergetvar name='user_icq' uid=$uid}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The user variable.
 */
function smarty_function_usergetvar($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])  ? $params['assign']   : null;
    $default = isset($params['default']) ? $params['default']  : null;
    $name    = isset($params['name'])    ? $params['name']     : null;
    $uid     = isset($params['uid'])     ? (int)$params['uid'] : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('usergetvar', 'name')));
        return false;
    }

    if ($uid) {
        $result = UserUtil::getVar($name, $uid, $default);
    } else {
        $result = UserUtil::getVar($name, -1, $default);
    }

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

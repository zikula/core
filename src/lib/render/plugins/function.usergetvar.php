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
 * Smarty function to get a user variable
 *
 * This function obtains a user-specific variable from the Zikula system.
 *
 * Note that the results should be handled by the varprepfordisplay of the
 * varprephtmldisplay modifiers before being displayed.
 *
 *
 * Available parameters:
 *   - name:    The name of the variable being requested
 *   - uid:     The user id to obtain the variable for - this parameter is optional
 *   - assign:  If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {usergetvar name='user_icq' uid=1|varprepfordisplay}
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $name        The name of the parameter being requested
 * @param        integer     $uid         The user id to obtain the variable for - this parameter is optional
 * @return       string      The user variable
 */
function smarty_function_usergetvar($params, &$smarty)
{
    $assign  = isset($params['assign'])  ? $params['assign']   : null;
    $default = isset($params['default']) ? $params['default']  : null;
    $name    = isset($params['name'])    ? $params['name']     : null;
    $uid     = isset($params['uid'])     ? (int)$params['uid'] : null;

    if (!$name) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('usergetvar', 'name')));
        return false;
    }

    if ($uid) {
        $result = UserUtil::getVar($name, $uid, $default);
    } else {
        $result = UserUtil::getVar($name, -1, $default);
    }

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}

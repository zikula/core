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
 * Smarty function to delete a session variable
 *
 * This function deletes a session-specific variable from the Zikula system.
 *
 *
 * Available parameters:
 *   - name:    The name of the session variable to delete
 *   - assign:  If set, the result is assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {sessiondelvar name='foobar'}
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $smarty     Reference to the Smarty object
 * @param        string      $name        The name of the session variable to delete
 * @return       string      The session variable
 */
function smarty_function_sessiondelvar($params, $smarty)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $name    = isset($params['name'])    ? $params['name']    : null;
    $default = isset($params['default']) ? $params['default'] : null;
    $path    = isset($params['path'])    ? $params['path']    : '/';

    if (!$name) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('sessiondelvar', 'name')));
        return false;
    }

    $result = SessionUtil::delVar($name, $default, $path);

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}

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
 * Smarty function to get a session variable
 *
 * This function obtains a session-specific variable from the Zikula system.
 *
 * Note that the results should be handled by the safetext or the safehtml
 * modifier before being displayed.
 *
 *
 * Available parameters:
 *   - name:    The name of the session variable to obtain
 *   - assign:  If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {sessiongetvar name='foobar'|safetext}
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $smarty     Reference to the Smarty object
 * @param        string      $name        The name of the session variable to obtain
 * @param        string      $default     (optional) The default value to return if the session variable is not set
 * @return       string      The session variable
 */
function smarty_function_sessiongetvar($params, $smarty)
{
    $assign               = isset($params['assign'])               ? $params['assign']               : null;
    $default              = isset($params['default'])              ? $params['default']              : null;
    $name                 = isset($params['name'])                 ? $params['name']                 : null;
    $path                 = isset($params['path'])                 ? $params['path']                 : '/';
    $autocreate           = isset($params['autocreate'])           ? $params['autocreate']           : true;
    $overwriteExistingVar = isset($params['overwriteExistingVar']) ? $params['overwriteExistingVar'] : false;

    if (!$name) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('sessiongetvar', 'name')));
        return false;
    }

    $result = SessionUtil::getVar($name, $default, $path, $autocreate, $overwriteExistingVar);

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}

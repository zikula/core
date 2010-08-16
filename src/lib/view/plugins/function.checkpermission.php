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
 * Check permission.
 *
 * Example:
 * {checkpermission comp="News::" inst=".*" level="ACCESS_ADMIN" assign="auth"}
 *
 * True/false will be returned.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return boolean Authorized?
 */
function smarty_function_checkpermission($params, $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $level  = isset($params['level'])  ? $params['level']  : null;
    // allow 1.2-style parameters (component/instance) as well as 1.1 and 1.3 (comp/inst)
    // align function.checkpermission.php with block.checkpermissionblock.php
    // if a and !b, if !a and b, if a and b, if !a and !b
    if (isset($params['comp'])  && !isset($params['component'])) $comp = $params['comp'];
    if (!isset($params['comp']) && isset($params['component']))  $comp = $params['component'];
    if (isset($params['comp'])  && isset($params['component']))  $comp = $params['comp'];
    if (!isset($params['comp']) && !isset($params['component'])) $comp = null;
    if (isset($params['inst'])  && !isset($params['instance']))  $inst = $params['inst'];
    if (!isset($params['inst']) && isset($params['instance']))   $inst = $params['instance'];
    if (isset($params['inst'])  && isset($params['instance']))   $inst = $params['inst'];
    if (!isset($params['inst']) && !isset($params['instance']))  $inst = null;

    if (!$comp) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_secauthaction', 'comp')));
        return false;
    }

    if (!$inst) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_secauthaction', 'inst')));
        return false;
    }

    if (!$level) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_secauthaction', 'level')));
        return false;
    }

    $result = SecurityUtil::checkPermission($comp, $inst, constant($level));

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

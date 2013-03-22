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
 * {checkpermission component="News::" instance=".*" level="ACCESS_ADMIN" assign="auth"}
 *
 * True/false will be returned.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return boolean
 */
function smarty_function_checkpermission($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $level  = isset($params['level'])  ? $params['level']  : null;

    if (isset($params['component'])) {
        $comp = $params['component'];
    } elseif (isset($params['comp'])) {
        LogUtil::log(__f('Warning! The {checkpermission} parameter %1$s is deprecated. Please use %2$s instead.', array('comp', 'component')), E_USER_DEPRECATED);
        $comp = $params['comp'];
    } else {
        $comp = null;
    }
    if (isset($params['instance'])) {
        $inst = $params['instance'];
    } elseif (isset($params['inst'])) {
        LogUtil::log(__f('Warning! The {checkpermission} parameter %1$s is deprecated. Please use %2$s instead.', array('inst', 'instance')), E_USER_DEPRECATED);
        $inst = $params['inst'];
    } else {
        $inst = null;
    }

    if (!isset($comp)) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_checkpermission', 'comp')));

        return false;
    }

    if (!isset($inst)) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_checkpermission', 'inst')));

        return false;
    }

    if (!$level) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_checkpermission', 'level')));

        return false;
    }

    $result = SecurityUtil::checkPermission($comp, $inst, constant($level));

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

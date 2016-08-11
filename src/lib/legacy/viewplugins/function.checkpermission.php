<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Check permission.
 *
 * Example:
 * {checkpermission component="News::" instance=".*" level="ACCESS_ADMIN" assign="auth"}
 *
 * True/false will be returned.
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return boolean
 */
function smarty_function_checkpermission($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $level  = isset($params['level']) ? $params['level'] : null;

    if (isset($params['component'])) {
        $comp = $params['component'];
    } elseif (isset($params['comp'])) {
        LogUtil::log(__f('Warning! The {checkpermission} parameter %1$s is deprecated. Please use %2$s instead.', ['comp', 'component']), E_USER_DEPRECATED);
        $comp = $params['comp'];
    } else {
        $comp = null;
    }
    if (isset($params['instance'])) {
        $inst = $params['instance'];
    } elseif (isset($params['inst'])) {
        LogUtil::log(__f('Warning! The {checkpermission} parameter %1$s is deprecated. Please use %2$s instead.', ['inst', 'instance']), E_USER_DEPRECATED);
        $inst = $params['inst'];
    } else {
        $inst = null;
    }

    if (!isset($comp)) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_function_checkpermission', 'comp']));

        return false;
    }

    if (!isset($inst)) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_function_checkpermission', 'inst']));

        return false;
    }

    if (!$level) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_function_checkpermission', 'level']));

        return false;
    }

    $result = SecurityUtil::checkPermission($comp, $inst, constant($level));

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

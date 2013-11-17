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
 * Example:
 * {secauthaction comp="Stories::" inst=".*" level="ACCESS_ADMIN" assign="auth"}
 *
 * true/false will be returned.
 *
 * This file is a plugin for Zikula_View, the Zikula implementation of Smarty
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       boolean     authorized?
 */
function smarty_function_secauthaction($params, &$smarty)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('secauthaction', 'checkpermission')), E_USER_DEPRECATED);

    $assign = isset($params['assign']) ? $params['assign'] : null;
    $comp   = isset($params['comp'])   ? $params['comp']   : null;
    $inst   = isset($params['inst'])   ? $params['inst']   : null;
    $level  = isset($params['level'])  ? $params['level']  : null;

    if (!$comp) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_secauthaction', 'comp')));

        return false;
    }

    if (!$inst) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_secauthaction', 'inst')));

        return false;
    }

    if (!$level) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_function_secauthaction', 'level')));

        return false;
    }

    $result = SecurityUtil::checkPermission($comp, $inst, constant($level));

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}

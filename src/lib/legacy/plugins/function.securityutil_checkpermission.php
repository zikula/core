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
 * {securityutil_checkpermission component='Users::' instance='.*' level='ACCESS_ADMIN' assign='auth'}
 *
 * true/false will be returned.
 *
 * This file is a plugin for Zikula_View, the Zikula implementation of Smarty
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $smarty      Reference to the Smarty object
 * @return       boolean     authorized?
 */
function smarty_function_securityutil_checkpermission($params, $smarty)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('securityutil_checkpermission', 'checkpermission')), E_USER_DEPRECATED);

    if (!isset($params['component'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('securityutil_checkpermission', 'component')));

        return false;
    }

    if (!isset($params['instance'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('securityutil_checkpermission', 'instance')));

        return false;
    }

    if (!isset($params['level'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('securityutil_checkpermission', 'level')));

        return false;
    }

    $result = SecurityUtil::checkPermission($params['component'], $params['instance'], constant($params['level']));

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}

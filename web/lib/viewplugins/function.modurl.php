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
 * Zikula_View function to create a zikula.orgpatible URL for a specific module function.
 *
 * This function returns a module URL string if successful. Unlike the API
 * function ModURL, this is already sanitized to display, so it should not be
 * passed to the safetext modifier.
 *
 * Available parameters:
 *   - modname:  The well-known name of a module for which to create the URL (required)
 *   - type:     The type of function for which to create the URL; currently one of 'user' or 'admin' (default is 'user')
 *   - func:     The actual module function for which to create the URL (default is 'main')
 *   - fragment: The fragement to target within the URL
 *   - ssl:      See below
 *   - fqurl:    Make a fully qualified URL
 *   - forcelongurl: Do not create a short URL (forced)
 *   - forcelang (boolean|string) Force the inclusion of the $forcelang or default system language in the generated url
 *   - append:   (optional) A string to be appended to the URL
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - all remaining parameters are passed to the module function
 *
 * Example
 * Create a URL to the News 'view' function with parameters 'sid' set to 3
 *   <a href="{modurl modname='News' type='user' func='display' sid='3'}">Link</a>
 *
 * Example SSL
 * Create a secure https:// URL to the News 'view' function with parameters 'sid' set to 3
 * ssl - set to constant null,true,false NOTE: $ssl = true not $ssl = 'true'  null - leave the current status untouched, true - create a ssl url, false - create a non-ssl url
 *   <a href="{modurl modname='News' type='user' func='display' sid='3' ssl=true}">Link</a>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The URL.
 */
function smarty_function_modurl($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $append = isset($params['append']) ? $params['append'] : '';
    $fragment = isset($params['fragment']) ? $params['fragment'] : null;
    $fqurl = isset($params['fqurl']) ? $params['fqurl'] : null;
    $forcelongurl = isset($params['forcelongurl']) ? (bool)$params['forcelongurl'] : false;

    if (isset($params['func']) && $params['func']) {
        $func = $params['func'];
    } else {
        if (System::isLegacyMode()) {
            $func = 'main';
            LogUtil::log(__f('{modurl} - %1$s is a required argument, you must specify it explicitly in %2$s', array('func', $view->template)), E_USER_DEPRECATED);
        } else {
            $view->trigger_error(__f('{modurl} - %1$s is a required argument, you must specify it explicitly in %2$s', array('func', $view->template)));
            return false;
        }
    }

    if (isset($params['type']) && $params['type']) {
        $type = $params['type'];
    } else {
        if (System::isLegacyMode()) {
            $type = 'user';
            LogUtil::log(__f('{modurl} - %1$s is a required argument, you must specify it explicitly in %2$s', array('type', $view->template)), E_USER_DEPRECATED);
        } else {
            $view->trigger_error(__f('{modurl} - %1$s is a required argument, you must specify it explicitly in %2$s', array('type', $view->template)));
            return false;
        }
    }

    $modname = isset($params['modname']) ? $params['modname'] : null;
    $ssl = isset($params['ssl']) ? (bool)$params['ssl'] : null;
    $forcelang = isset($params['forcelang']) && $params['forcelang'] ? $params['forcelang'] : false;


    // avoid passing these to ModUtil::url
    unset($params['modname']);
    unset($params['type']);
    unset($params['func']);
    unset($params['fragment']);
    unset($params['ssl']);
    unset($params['fqurl']);
    unset($params['assign']);
    unset($params['append']);
    unset($params['forcelang']);
    unset($params['forcelongurl']);

    if (!$modname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('modurl', 'modname')));
        return false;
    }

    $result = ModUtil::url($modname, $type, $func, $params, $ssl, $fragment, $fqurl, $forcelongurl, $forcelang);

    if ($append && is_string($append)) {
        $result .= $append;
    }

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return DataUtil::formatForDisplay($result);
    }
}

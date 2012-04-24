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
 * Zikula_View function to get all session variables.
 *
 * This function gets all session vars from the Zikula system assigns the names and
 * values to two array. This is being used in pndebug to show them.
 *
 * Example
 *   {debugenvironment}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return void
 */
function smarty_function_debugenvironment($params, Zikula_View $view)
{
    $view->assign('_ZSession_keys', array_keys($_SESSION) );
    $view->assign('_ZSession_vals', array_values($_SESSION) );

    $view->assign('_smartyversion', $view->_version);
    $_theme = ModUtil::getInfoFromName('Theme');
    $view->assign('_themeversion', $_theme['version']);

    $view->assign('_force_compile', (ModUtil::getVar('Theme', 'force_compile')) ? __('On') : __('Off'));
    $view->assign('_compile_check', (ModUtil::getVar('Theme', 'compile_check')) ? __('On') : __('Off'));

    $view->assign('_baseurl', System::getBaseUrl());
    $view->assign('_baseuri', System::getBaseUri());

    $plugininfo = isset($view->_plugins['function']['zdebug']) ? $view->_plugins['function']['zdebug'] : $view->_plugins['function']['zpopup'];

    $view->assign('_template', $plugininfo[1]);
    $view->assign('_path',     $view->get_template_path($plugininfo[1]));
    $view->assign('_line',     $plugininfo[2]);
}

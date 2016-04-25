<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    $view->assign('_ZSession_keys', array_keys($_SESSION));
    $view->assign('_ZSession_vals', array_values($_SESSION));

    $view->assign('_smartyversion', $view->_version);
    $_theme = ModUtil::getInfoFromName('ZikulaThemeModule');
    $view->assign('_themeversion', $_theme['version']);

    $view->assign('_force_compile', (ModUtil::getVar('ZikulaThemeModule', 'force_compile')) ? __('On') : __('Off'));
    $view->assign('_compile_check', (ModUtil::getVar('ZikulaThemeModule', 'compile_check')) ? __('On') : __('Off'));

    $view->assign('_baseurl', System::getBaseUrl());
    $view->assign('_baseuri', System::getBaseUri());

    $plugininfo = isset($view->_plugins['function']['zdebug']) ? $view->_plugins['function']['zdebug'] : $view->_plugins['function']['zpopup'];

    $view->assign('_template', $plugininfo[1]);
    $view->assign('_path', $view->get_template_path($plugininfo[1]));
    $view->assign('_line', $plugininfo[2]);
}

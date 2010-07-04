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
 * Smarty function to get all session variables.
 *
 * This function gets all session vars from the Zikula system assigns the names and
 * values to two array. This is being used in pndebug to show them.
 *
 * Example
 *   {debugenvironment}
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 *
 * @return void
 */
function smarty_function_debugenvironment($params, &$smarty)
{
    $smarty->assign('_ZSession_keys', array_keys($_SESSION) );
    $smarty->assign('_ZSession_vals', array_values($_SESSION) );

    $smarty->assign('_smartyversion', $smarty->_version);
    $_theme = ModUtil::getInfoFromName('Theme');
    $smarty->assign('_themeversion', $_theme['version']);

    $smarty->assign('_force_compile', (ModUtil::getVar('Theme', 'force_compile')) ? __('On') : __('Off'));
    $smarty->assign('_compile_check', (ModUtil::getVar('Theme', 'compile_check')) ? __('On') : __('Off'));

    $smarty->assign('_baseurl', System::getBaseUrl());
    $smarty->assign('_baseuri', System::getBaseUri());

    $smarty->assign('_template', $smarty->_plugins['function']['zdebug'][1]);
    $smarty->assign('_path',    $smarty->get_template_path($smarty->_plugins['function']['zdebug'][1]));
    $smarty->assign('_line',    $smarty->_plugins['function']['zdebug'][2]);
}

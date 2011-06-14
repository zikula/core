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
 * Zikula_View function to display a Zikula specific debug popup
 *
 * This function shows a Zikula debug popup if the user has sufficient access rights
 *
 * You need the following permission to see this:
 *   ModuleName::debug | .* | ACCESS_ADMIN
 *
 * This plugin is an alias of zdebug but with a JavaScript popup output
 *
 * Example
 *   { zpopup }
 *
 * Parameters:
 *  width:      Width of the console UI.Window (default: 580)
 *  height:     Height of the console UI.Window (default: 600)
 *  checkpermission: If false, then a security check is not performed, allowing debug information to
 *              be displayed, for example, when there is no user logged in. Development mode
 *              must also be enabled. Defaults to true;
 *  template Specify different debug template, default zdebug.tpl,
 *              must be stored in system/Theme/templates.
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string Debug output.
 */
function smarty_function_zpopup($params, Zikula_View $view)
{
    $params['popup'] = true;

    // invoke zdebug to add the popup to the page header
    include_once('lib/viewplugins/function.zdebug.php');
    smarty_function_zdebug($params, $view);

    return;
}

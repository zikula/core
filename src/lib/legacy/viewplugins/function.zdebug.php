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
 * Zikula_View function to display a Zikula specific debug Zikula.UI.Window
 *
 * This function shows a Zikula debug window if the user has sufficient access rights
 *
 * You need the following permission to see this:
 *   ModuleName::debug | .* | ACCESS_ADMIN
 *
 * This plugin is basing on the original debug plugin written by Monte Ohrt <monte@ispi.net>
 *
 * Examples
 *   { zdebug }
 *   { zdebug width='400' }
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
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string Debug output
 */
function smarty_function_zdebug($params, Zikula_View $view)
{
    $zdebug = '';
    $thismodule = ModUtil::getName();
    $skipPermissionCheck = System::isDevelopmentMode() && isset($params['checkpermission']) && !$params['checkpermission'];

    if ($skipPermissionCheck || SecurityUtil::checkPermission($thismodule.'::debug', '::', ACCESS_ADMIN)) {
        // backup and modify the view attributes
        $_template_dir_orig = $view->template_dir;
        $_default_resource_type_orig = $view->default_resource_type;
        $_plugins_outputfilter = $view->_plugins['outputfilter'];
        $_compile_id_orig   = $view->_compile_id;

        $view->template_dir = 'system/ThemeModule/Resources/views';
        $view->default_resource_type = 'file';
        $view->_plugins['outputfilter'] = null;
        $view->_compile_id  = null;

        $width  = isset($params['width']) && is_int($params['width']) ? $params['width'] : 580;
        $height = isset($params['height']) && is_int($params['height']) ? $params['height'] : 600;
        $popup  = isset($params['popup']) ? (bool)$params['popup'] : false;

        // figure out the template to use
        if (isset($params['template']) && !empty($params['template'])) {
            if (is_readable($view->template_dir . '/' . $params['template'])) {
                $view->debug_tpl = $params['template'];
            }
        } else {
            $view->debug_tpl = $popup ? 'zpopup.tpl' : 'zdebug.tpl';
        }

        // get the zdebug output
        $zdebug = $view->assign('zdebugwidth', $width)
                       ->assign('zdebugheight', $height)
                       ->assign('zdebugpopup', $popup)
                       ->_fetch($view->debug_tpl);

        // restore original values
        $view->_compile_id = $_compile_id_orig;
        $view->template_dir = $_template_dir_orig;
        $view->default_resource_type = $_default_resource_type_orig;
        $view->_plugins['outputfilter'] = $_plugins_outputfilter;
    }

    return $zdebug;
}

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
 * Zikula_View function to display a Zikula specific debug window
 *
 * This function shows a Zikula debug window if the user has sufficient access rights
 *
 * You need to have:
 * modulename::debug     .*     ACCESS_ADMIN
 * permission to see this.
 *
 * This plugin is basing on the original debug plugin written by Monte Ohrt <monte@ispi.net>
 *
 * Example
 *   { pndebug }
 *
 * Parameters:
 *  output   If html, show debug in rendered page, otherwise open popup window
 *  template Specify different debug template, default zdebug.tpl,
 *                                        must be stored in Theme/pntemplates.
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Zikula_View $view Reference to the Zikula_View object.
 *
 * @return string debug output
 */
function smarty_function_zdebug($params, $view)
{
    $out = '';
    $thismodule = ModUtil::getName();
    if (SecurityUtil::checkPermission($thismodule.'::debug', '::', ACCESS_ADMIN)) {
        if (isset($params['output']) && !empty($params['output'])) {
            $view->assign('_smarty_debug_output', $params['output']);
        }

        $modinfo = ModUtil::getInfoFromName('Theme');
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        $osmoddir = DataUtil::formatForOS($modinfo['directory']);

        $_template_dir_orig = $view->template_dir;
        $_default_resource_type_orig = $view->default_resource_type;

        $view->template_dir = (is_dir("$modpath/$osmoddir/templates") ? "$modpath/$osmoddir/templates" : "$modpath/$osmoddir/pntemplates");
        $view->default_resource_type = 'file';
        $view->_plugins['outputfilter'] = null;

        if (isset($params['template']) && !empty($params['template'])) {
            $debug_tpl = $view->template_dir . '/' . $params['template'];
            if (is_readable($debug_tpl)) {
                $view->debug_tpl = $params['template'];
            }
        } else {
            $view->debug_tpl = 'zdebug.tpl';
        }

        if ($view->security && is_file($view->debug_tpl)) {
            $view->secure_dir[] = dirname(realpath($view->debug_tpl));
        }

        $_compile_id_orig = $view->_compile_id;
        $view->_compile_id = null;

        $_compile_path = $view->_get_compile_path($view->debug_tpl);
        if ($view->_compile_resource($view->debug_tpl, $_compile_path)) {
            ob_start();
            $view->_include($_compile_path);
            $out = ob_get_contents();
            ob_end_clean();
        }

        $view->_compile_id = $_compile_id_orig;
        $view->template_dir = $_template_dir_orig;
        $view->default_resource_type = $_default_resource_type_orig;
    }

    return $out;
}

<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display a Zikula specific debug window
 *
 * This function shows a Zikula debug window if the user has sufficient access rights
 *
 * You need to have:
 * modulename::debug     .*     ACCESS_ADMIN
 * permission to see this.
 *
 *
 * Example
 *   { pndebug }
 *
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $output      if html, show debug in rendered page, otherwise open popup window
 * @param        string      $template    specify different debug template, default pndebug.html,
 *                                        must be stored in Theme/pntemplates
 * @return       string      debug output
 *
 * This plugin is basing on the original debug plugin written by Monte Ohrt <monte@ispi.net>
 */
function smarty_function_zdebug($params, &$smarty)
{
    $out = '';
    $thismodule = ModUtil::getName();
//    if (SecurityUtil::checkPermission($thismodule.'::debug', '::', ACCESS_ADMIN))
//    {
        if (isset($params['output']) && !empty($params['output'])) {
            $smarty->assign('_smarty_debug_output', $params['output']);
        }

        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName('Theme'));
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        $osmoddir = DataUtil::formatForOS($modinfo['directory']);

        $_template_dir_orig = $smarty->template_dir;
        $_default_resource_type_orig = $smarty->default_resource_type;

        $smarty->template_dir = (is_dir("$modpath/$osmoddir/templates") ? "$modpath/$osmoddir/templates" : "$modpath/$osmoddir/pntemplates");
        $smarty->default_resource_type = 'file';
        $smarty->_plugins['outputfilter'] = null;

        if (isset($params['template']) && !empty($params['template'])) {
            $debug_tpl = $smarty->template_dir . '/' . $params['template'];
            if (is_readable($debug_tpl)) {
                $smarty->debug_tpl = $params['template'];
            }
        } else {
            $smarty->debug_tpl = 'zdebug.html';
        }

        if ($smarty->security && is_file($smarty->debug_tpl)) {
            $smarty->secure_dir[] = dirname(realpath($smarty->debug_tpl));
        }

        $_compile_id_orig = $smarty->_compile_id;
        $smarty->_compile_id = null;

        $_compile_path = $smarty->_get_compile_path($smarty->debug_tpl);
        if ($smarty->_compile_resource($smarty->debug_tpl, $_compile_path)) {
            ob_start();
            $smarty->_include($_compile_path);
            $out = ob_get_contents();
            ob_end_clean();
        }

        $smarty->_compile_id = $_compile_id_orig;
        $smarty->template_dir = $_template_dir_orig;
        $smarty->default_resource_type = $_default_resource_type_orig;
//    }

    return $out;
}

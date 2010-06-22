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
 * Smarty function to retrieve module information
 *
 * This function retrieves module information from the database and returns them
 * or assigns them to a variable for later use
 *
 *
 * Available parameters:
 *   - info        the information you want to retrieve from the modules info,
 *                 "all" results in assigning all information, see $assign
 *   - assign      (optional or mandatory :-)) if set, assign the result instead of returning it
 *                 if $info is "all", a $assign is mandatory and the default is modinfo
 *   - modname     (optional) module name, if not set, the recent module is used
 *   - modid       (optional) module id, if not set, the recent module is used
 *
 * Example
 *   {modgetinfo info='displayname'}
 *   {modgetinfo info='all' assign='gimmeeverything'}
 *   {modgetinfo modname='anyymodname' info='all' assign='gimmeeverything'}
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      The module variable
 */
function smarty_function_modgetinfo($params, &$smarty)
{
    $assign  = isset($params['assign'])  ? $params['assign']     : null;
    $info    = isset($params['info'])    ? $params['info']       : null;
    $modid   = isset($params['modid'])   ? (int)$params['modid'] : 0;
    $modname = isset($params['modname']) ? $params['modname']    : null;
    $default = isset($params['default']) ? $params['default']    : false;

    if (!$modid) {
        $modname = $modname ? $modname : ModUtil::getName();
        if (!ModUtil::available($modname)) {
            if ($assign) {
                $smarty->assign($assign, $default);
                return false;
            }
            $smarty->assign($assign, $default);
            return;
        }
        $modid = ModUtil::getIdFromName($modname);
    }
    $modinfo = ModUtil::getInfo($modid);

    $info = strtolower($info);
    if ($info != 'all' && !isset($modinfo[$info])) {
        $smarty->trigger_error(__f('Invalid %1$s [%2$s] passed to %3$s.', array('info', $info, 'modgetinfo')));
        return false;
    }

    if ($info == 'all') {
        $assign = ($assign ? $assign : 'modinfo');
        $smarty->assign($assign, $modinfo);
    } else {
        if ($assign) {
            $smarty->assign($assign, $modinfo[$info]);
        } else {
            return DataUtil::formatForDisplay($modinfo[$info]);
        }
    }
}

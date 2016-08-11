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
 * Zikula_View function to retrieve module information
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
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The module variable
 */
function smarty_function_modgetinfo($params, Zikula_View $view)
{
    $assign  = isset($params['assign']) ? $params['assign'] : null;
    $info    = isset($params['info']) ? $params['info'] : null;
    $modid   = isset($params['modid']) ? (int)$params['modid'] : 0;
    $modname = isset($params['modname']) ? $params['modname'] : null;
    $default = isset($params['default']) ? $params['default'] : false;

    if (!$modid) {
        $modname = $modname ? $modname : ModUtil::getName();
        if (!ModUtil::available($modname)) {
            if ($assign) {
                $view->assign($assign, $default);

                return false;
            }
            $view->assign($assign, $default);

            return;
        }
        $modid = ModUtil::getIdFromName($modname);
    }
    $modinfo = ModUtil::getInfo($modid);

    $info = strtolower($info);
    if ($info != 'all' && !isset($modinfo[$info])) {
        $view->trigger_error(__f('Invalid %1$s [%2$s] passed to %3$s.', ['info', $info, 'modgetinfo']));

        return false;
    }

    if ($info == 'all') {
        $assign = ($assign ? $assign : 'modinfo');
        $view->assign($assign, $modinfo);
    } else {
        if ($assign) {
            $view->assign($assign, $modinfo[$info]);
        } else {
            return DataUtil::formatForDisplay($modinfo[$info]);
        }
    }
}

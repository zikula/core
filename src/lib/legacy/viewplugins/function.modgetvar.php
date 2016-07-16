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
 * Zikula_View function to get module variable
 *
 * This function obtains a module-specific variable from the Zikula system.
 *
 * Note that the results should be handled by the safetext or the safehtml
 * modifier before being displayed.
 *
 *
 * Available parameters:
 *   - module:   The well-known name of a module from which to obtain the variable
 *   - name:     The name of the module variable to obtain
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - html:     If true then result will be treated as html content
 *   - default:  The default value to return if the config variable is not set
 *
 * Example
 *   {modgetvar module='Example' name='foobar' assign='foobarOfExample'}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The module variable
 */
function smarty_function_modgetvar($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])  ? $params['assign']     : null;
    $default = isset($params['default']) ? $params['default']    : null;
    $module  = isset($params['module'])  ? $params['module']     : null;
    $html    = isset($params['html'])    ? (bool)$params['html'] : false;
    $name    = isset($params['name'])    ? $params['name']       : null;

    if (!$module) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['modgetvar', 'module']));

        return false;
    }

    if (!$name && !$assign) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['modgetvar', 'name']));

        return false;
    }

    if (!$name) {
        $result = ModUtil::getVar($module);
    } else {
        $result = ModUtil::getVar($module, $name, $default);
    }

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        if ($html) {
            return DataUtil::formatForDisplayHTML($result);
        } else {
            return DataUtil::formatForDisplay($result);
        }
    }
}

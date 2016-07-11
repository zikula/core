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
 * Plugin to get a variable from the theme
 *
 * This function returns the corresponding value set on the theme
 *
 * Available parameters:
 *   - name:    Name of the variable
 *   - default: If set, the default value to return if the variable is not set
 *   - assign:  If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 * {themegetvar name='themepath'}
 * {themegetvar name='scriptpath' assign='scriptpath'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The colour definition.
 */
function smarty_function_themegetvar($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $default = isset($params['default']) ? $params['default'] : null;
    $name    = isset($params['name'])    ? $params['name']    : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['themegetvar', 'name']));

        return false;
    }

    $result = ThemeUtil::getVar($name, $default);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

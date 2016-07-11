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
 * Plugin to set a variable on the theme
 *
 * This function set the corresponding value on a theme variable
 *
 * Available parameters:
 *   - name:    Name of the variable
 *   - value:   The value to set on the variable
 *
 * Example
 * {themesetvar name='master' value='1col'} for Andreas08
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return mixed
 */
function smarty_function_themesetvar($params, Zikula_View $view)
{
    $name   = isset($params['name'])   ? $params['name']    : null;
    $value  = isset($params['value'])  ? $params['value'] : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['themegetvar', 'name']));

        return false;
    }

    ThemeUtil::setVar($name, $value);
}

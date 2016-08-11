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
 * Zikula_View function to set page variable
 *
 * This function obtains a page-specific variable from the Zikula system.
 *
 * Available parameters:
 *   - name:     The name of the page variable to set
 *   - value:    The value of the page variable to set
 *
 * Zikula doesn't impose any restriction on the page variable's name except for duplicate
 * and reserved names. As of this writing, the list of reserved names consists of
 * <ul>
 * <li>title</li>
 * <li>stylesheet</li>
 * <li>javascript</li>
 * <li>body</li>
 * <li>header</li>
 * <li>footer</li>
 * </ul>
 *
 * In addition, if your system is operating in legacy compatibility mode, then
 * the variable 'rawtext' is reserved, and maps to 'header'. (When not operating in
 * legacy compatibility mode, 'rawtext' is not reserved and will not be rendered
 * to the page output by the page variable output filter.)
 *
 * Example
 *   {pagesetvar name="title" value="mytitle"}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string
 */
function smarty_function_pagesetvar($params, Zikula_View $view)
{
    $name  = isset($params['name']) ? $params['name'] : null;
    $value = isset($params['value']) ? $params['value'] : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['pagesetvar', 'name']));

        return false;
    }
    if (!$value) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['pagesetvar', 'value']));

        return false;
    }

    // handle Clip which is manually loading a Theme's stylesheets
    if ($name == 'stylesheet' && false !== strpos($value, 'system/Theme/style/')) {
        $value = str_replace('system/Theme/style/', 'system/ThemeModule/Resources/public/css/', $value);
    }

    if (in_array($name, ['stylesheet', 'javascript'])) {
        $value = explode(',', $value);
    }

    PageUtil::setVar($name, $value);
}

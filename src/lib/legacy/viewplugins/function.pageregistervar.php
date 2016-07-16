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
 * Zikula_View function to register a page variable
 *
 * This function registers a page-specific variable with the Zikula system.
 *
 * Available parameters:
 *   - name:     The name of the page variable to obtain
 *
 * Zikula doesn't impose any restriction on the page variable's name except for duplicate
 * and reserved names. As of this writing, the list of reserved names consists of
 * <ul>
 * <li>title</li>
 * <li>description</li>
 * <li>keywords</li>
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
 *   {pageregistervar name='title'}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The module variable
 */
function smarty_function_pageregistervar($params, Zikula_View $view)
{
    $name =       isset($params['name']) ? $params['name'] : null;
    $multivalue = isset($params['multivalue']) ? $params['multivalue'] : null;
    $default =    isset($params['default']) ? $params['default'] : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['pageregistervar', 'name']));

        return false;
    }

    PageUtil::registerVar($name, $multivalue, $default);

    return;
}

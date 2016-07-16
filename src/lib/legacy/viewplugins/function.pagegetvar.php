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
 * Zikula_View function to get page variable
 *
 * This function obtains a page-specific variable from the Zikula system.
 *
 * Available parameters:
 *   - name:     The name of the page variable to obtain
 *   - html:     If true then result will be treated as html content.
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
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
 *   {pagegetvar name='title'}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The module variable
 */
function smarty_function_pagegetvar($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign']     : null;
    $html   = isset($params['html'])   ? (bool)$params['html'] : false;
    $name   = isset($params['name'])   ? $params['name']       : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['pagegetvar', 'name']));

        return false;
    }

    $result = PageUtil::getVar($name);

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

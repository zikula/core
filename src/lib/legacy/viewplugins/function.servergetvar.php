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
 * This function obtains a server-specific variable from the system.
 *
 * Note that the results should be handled by the safetext or the safehtml
 * modifier before being displayed.
 *
 *
 * Available parameters:
 *   - name:     The name of the module variable to obtain
 *   - assign:   (optional) If set then result will be assigned to this template variable
 *   - default:  (optional) The default value to return if the server variable is not set
 *
 * Example
 *   {servergetvar name='PHP_SELF'}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The module variable
 */
function smarty_function_servergetvar($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $default = isset($params['default']) ? $params['default'] : null;
    $name    = isset($params['name'])    ? $params['name']    : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['System::serverGetVar', 'name']));

        return false;
    }

    $result = System::serverGetVar($name, $default);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return DataUtil::formatForDisplay($result);
    }
}

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
 * Smarty function to set a session variable
 *
 * This function sets a session-specific variable in the Zikula system.
 *
 * Note that the results should be handled by the safetext or the safehtml
 * modifier before being displayed.
 *
 *
 * Available parameters:
 *   - name:    The name of the session variable to obtain
 *   - value:   The value for the session variable
 *   - assign:  If set, the result is assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {sessionsetvar name='foo' value='bar'}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Smarty object
 * @param string      $name   The name of the session variable to obtain
 *
 * @return mixed
 */
function smarty_function_sessionsetvar($params, Zikula_View $view)
{
    $assign  = isset($params['assign']) ? $params['assign'] : null;
    $name    = isset($params['name']) ? $params['name'] : null;
    $value   = isset($params['value']) ? $params['value'] : null;
    $path    = isset($params['path']) ? $params['path'] : '/';

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['sessionsetvar', 'name']));

        return false;
    }

    if (!$value) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['sessionsetvar', 'value']));

        return false;
    }

    $result = $view->getRequest()->getSession()->set($name, $value, $path);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

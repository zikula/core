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
 * Smarty function to get a session variable
 *
 * This function obtains a session-specific variable from the Zikula system.
 *
 * Note that the results should be handled by the safetext or the safehtml
 * modifier before being displayed.
 *
 *
 * Available parameters:
 *   - name:    The name of the session variable to obtain
 *   - assign:  If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {sessiongetvar name='foobar'|safetext}
 *
 * @param array       $params  All attributes passed to this function from the template
 * @param Zikula_View $view    Reference to the Smarty object
 * @param string      $name    The name of the session variable to obtain
 *
 * @return mixed
 */
function smarty_function_sessiongetvar($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $default = isset($params['default']) ? $params['default'] : null;
    $name    = isset($params['name'])    ? $params['name']    : null;
    $path    = isset($params['path'])    ? $params['path']    : '/';

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['sessiongetvar', 'name']));

        return false;
    }

    $result = $view->getRequest()->getSession()->get($name, $default, $path);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

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
 * Smarty function to delete a session variable
 *
 * This function deletes a session-specific variable from the Zikula system.
 *
 *
 * Available parameters:
 *   - name:    The name of the session variable to delete
 *   - path:    The namespace.
 *   - assign:  If set, the result is assigned to the corresponding variable instead of printed out
 *
 * Example
 *   {sessiondelvar name='foobar'}
 *
 * @param  array       $params All attributes passed to this function from the template
 * @param  Zikula_View $view   Reference to the Smarty object
 * @param  string      $name   The name of the session variable to delete
 *
 * @return mixed
 */
function smarty_function_sessiondelvar($params, Zikula_View $view)
{
    $assign  = isset($params['assign']) ? $params['assign'] : null;
    $name    = isset($params['name']) ? $params['name'] : null;
    $path    = isset($params['path']) ? $params['path'] : '/';

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['sessiondelvar', 'name']));

        return false;
    }

    $result = $view->getRequest()->getSession()->del($name, $path);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

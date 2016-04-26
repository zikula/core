<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_View function to to execute a PHP callable.
 *
 * This plugin can call any PHP callable using x_class + x_method OR x_function
 * with a list of argument/value pairs.
 *
 *
 * Available parameters:
 *   - x_class:    The well-known name of a module to execute a function from (required)
 *   - x_method:   The type of function to execute; currently one of 'user' or 'admin' (default is 'user')
 *   - x_function: The name of the module function to execute (default is 'index')
 *   - x_assign:     If set, the results are assigned to the corresponding variable instead of printed out
 *   - all remaining parameters are passed to the callable.
 *
 * Based on call_user_func_array()
 *
 * Example
 * {callfunc x_class='Foo' x_method='Bar' name='Jane'}
 * {callfunc x_method='Something' age=21 name='Jane'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return mixed The results of the callable.
 */
function smarty_function_callfunc($params, Zikula_View $view)
{
    $assign = (isset($params['x_assign']) && !empty($params['x_assign'])) ? $params['x_assign'] : '';

    if (array_key_exists('x_class', $params)) {
        $class = $params['x_class'];
        $method = $params['x_method'];
    } elseif (array_key_exists('x_function', $params)) {
        $function = $params['x_function'];
    } else {
        $view->trigger_error(__f('Error! in %1$s: the "class" and "method" parameter must be specified together or just "function" by itself.', ['calluserfunc', 'modname']));
    }

    $callable = (isset($class)) ? [$class, $method] : $function;

    unset($params['x_class']);
    unset($params['x_method']);
    unset($params['x_function']);
    unset($params['x_assign']);

    $result = call_user_func_array($callable, $params);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

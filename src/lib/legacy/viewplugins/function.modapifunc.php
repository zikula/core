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
 * Zikula_View function to to execute a module API function
 *
 * This function calls a calls a specific module API function. It returns whatever the return
 * value of the resultant function is if it succeeds.
 * Note that in contrast to the API function ModUtil::apiFunc you need not to load the
 * module API with ModUtil::loadApi.
 *
 *
 * Available parameters:
 *   - modname:  The well-known name of a module to execute a function from (required)
 *   - type:     The type of function to execute; currently one of 'user' or 'admin' (default is 'user')
 *   - func:     The name of the module function to execute (default is 'index')
 *   - assign:   The name of a variable to which the results are assigned
 *   - all remaining parameters are passed to the module API function
 *
 * Examples
 *   {modapifunc modname='News' type='user' func='get' sid='3'}
 *
 *   {modapifunc modname='foobar' type='user' func='getfoo' id='1' assign='myfoo'}
 *   {$myfoo.title}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    function.modfunc.php::smarty_function_modfunc()
 *
 * @return string The results of the module API function.
 */
function smarty_function_modapifunc($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])                  ? $params['assign']  : null;
    $func    = isset($params['func']) && $params['func'] ? $params['func']    : 'index';
    $modname = isset($params['modname'])                 ? $params['modname'] : null;
    $type    = isset($params['type']) && $params['type'] ? $params['type']    : 'user';

    // avoid passing these to ModUtil::apiFunc
    unset($params['modname']);
    unset($params['type']);
    unset($params['func']);
    unset($params['assign']);

    if (!$modname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['modapifunc', 'modname']));

        return false;
    }

    if (isset($params['modnamefunc'])) {
        $params['modname'] = $params['modnamefunc'];
        unset($params['modnamefunc']);
    }

    $result = ModUtil::apiFunc($modname, $type, $func, $params);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

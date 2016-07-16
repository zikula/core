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
 * Zikula_View function to to execute a module function
 *
 * This function calls a specific module function.  It returns whatever the return
 * value of the resultant function is if it succeeds.
 * Note that in contrast to the API function ModUtil::func you need not to load the
 * module with ModUtil::load.
 *
 *
 * Available parameters:
 *   - modname:  The well-known name of a module to execute a function from (required)
 *   - type:     The type of function to execute; currently one of 'user' or 'admin' (default is 'user')
 *   - func:     The name of the module function to execute (default is 'index')
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - all remaining parameters are passed to the module function
 *
 * Example
 * {modfunc modname='News' type='user' func='view'}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @see    function.ModUtil::apiFunc.php::smarty_function_modapifunc()
 *
 * @return string The results of the module function
 */
function smarty_function_modfunc($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])                  ? $params['assign']  : null;
    $func    = isset($params['func']) && $params['func'] ? $params['func']    : 'index';
    $modname = isset($params['modname'])                 ? $params['modname'] : null;
    $type    = isset($params['type']) && $params['type'] ? $params['type']    : 'user';
    $return  = isset($params['return'])                  ? $params['return']  : null;

    // avoid passing these to ModUtil::func
    unset($params['modname']);
    unset($params['type']);
    unset($params['func']);
    unset($params['assign']);

    if (!$modname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['modfunc', 'modname']));

        return false;
    }

    if (isset($params['modnamefunc'])) {
        $params['modname'] = $params['modnamefunc'];
        unset($params['modnamefunc']);
    }

    $result = ModUtil::func($modname, $type, $func, $params);
    if (is_array($result)) {
        $renderer = Zikula_View::getInstance($modname);
        $renderer->assign($result);
        if (isset($return['template'])) {
            echo $renderer->fetch($return['template']);
        } else {
            $modname = strtolower($modname);
            $type = strtolower($type);
            $func = strtolower($func);
            $result = $renderer->fetch("{$modname}_{$type}_{$func}.tpl");
        }
    }

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

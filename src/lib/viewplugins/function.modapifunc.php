<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
 *   - func:     The name of the module function to execute (default is 'main')
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
    $func    = isset($params['func']) && $params['func'] ? $params['func']    : 'main';
    $modname = isset($params['modname'])                 ? $params['modname'] : null;
    $type    = isset($params['type']) && $params['type'] ? $params['type']    : 'user';

    // avoid passing these to ModUtil::apiFunc
    unset($params['modname']);
    unset($params['type']);
    unset($params['func']);
    unset($params['assign']);

    if (!$modname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('modapifunc', 'modname')));

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

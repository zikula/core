<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to to execute a module function
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
 *   - func:     The name of the module function to execute (default is 'main')
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - all remaining parameters are passed to the module function
 *
 * Example
 * <!--[ModUtil::func modname='News' type='user' func='view']-->
 *
 * @author       Andreas Stratmann
 * @see          function.ModUtil::apiFunc.php::smarty_function_modapifunc()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the results of the module function
 */
function smarty_function_modfunc($params, &$smarty)
{
    $saveDomain = $smarty->renderDomain;
    $assign  = isset($params['assign'])                  ? $params['assign']  : null;
    $func    = isset($params['func']) && $params['func'] ? $params['func']    : 'main';
    $modname = isset($params['modname'])                 ? $params['modname'] : null;
    $type    = isset($params['type']) && $params['type'] ? $params['type']    : 'user';
    $return  = isset($params['return'])                  ? $params['return']  : null;

    // avoid passing these to ModUtil::func
    unset($params['modname']);
    unset($params['type']);
    unset($params['func']);
    unset($params['assign']);

    if (!$modname) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('ModUtil::func', 'modname')));
        return false;
    }

    if (isset($params['modnamefunc'])) {
        $params['modname'] = $params['modnamefunc'];
        unset($params['modnamefunc']);
    }

    $result = ModUtil::func($modname, $type, $func, $params);
    if (is_array($result)) {
        $pnRender = Renderer::getInstance($modname);
        $pnRender->assign($result);
        if (isset($return['template'])) {
            echo $pnRender->fetch($return['template']);
        } else {
            $modname = strtolower($modname);
            $type = strtolower($type);
            $func = strtolower($func);
            $result = $pnRender->fetch("{$modname}_{$type}_{$func}.htm");
        }
    }

    // ensure the renderDomain wasnt overwritten
    $smarty->renderDomain = $saveDomain;

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}

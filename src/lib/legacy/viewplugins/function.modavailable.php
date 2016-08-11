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
 * Zikula_View function to check for the availability of a module
 *
 * This function calls ModUtil::available to determine if a Zikula module is
 * is available. True is returned if the module is available, false otherwise.
 * The result can also be assigned to a template variable.
 *
 * Available parameters:
 *   - modname:  The well-known name of a module to execute a function from (required)
 *   - assign:   The name of a variable to which the results are assigned
 *
 * Examples
 *   {modavailable modname="News"}
 *
 *   {modavailable modname="foobar" assign="myfoo"}
 *   {if $myfoo}.....{/if}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @see    function.ModUtil::available.php::smarty_function_modavailable()
 *
 * @return boolean True if the module is available; false otherwise
 */
function smarty_function_modavailable($params, Zikula_View $view)
{
    $assign  = isset($params['assign']) ? $params['assign'] : null;
    $mod     = isset($params['mod']) ? $params['mod'] : null;
    $modname = isset($params['modname']) ? $params['modname'] : null;

    // minor backwards compatability
    if ($mod) {
        $modname = $mod;
    }

    $result = ModUtil::available($modname);

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

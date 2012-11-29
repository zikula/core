<?php
/**
 * Copyright Zikula Foundation 2012 - Zikula Application Framework
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
 * Zikula_View function to the admin image path of a module
 *
 * This function returns the path to the admin image of the current top-level
 * module if $modname is not set. Otherwise it returns the path to the admin
 * image of the given module.
 *
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding
 *               variable instead of printed out
 *   - modname:  The module to return the image path for 
 *               (defaults to top-level module)
 *
 * Example
 *   {modgetimage|safetext}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The path to the module's admin image
 */
function smarty_function_modgetimage($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $modname = isset($params['modname']) ? $params['modname'] : (ModUtil::getName());

    $path = ModUtil::getModuleImagePath($modname);

    if ($assign) {
        $view->assign($assign, $path);
    } else {
        return $path;
    }
}

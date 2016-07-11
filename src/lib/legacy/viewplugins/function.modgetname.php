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
 * Zikula_View function to the topmost module name
 *
 * This function currently returns the name of the current top-level
 * module, false if not in a module.
 *
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding
 *               variable instead of printed out
 *
 * Example
 *   {modgetname|safetext}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The module variable.
 */
function smarty_function_modgetname($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $result = ModUtil::getName();

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

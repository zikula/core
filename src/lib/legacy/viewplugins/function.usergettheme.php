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
 * Zikula_View function to the current users theme
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The variables content.
 */
function smarty_function_usergettheme($params, Zikula_View $view)
{
    $assign = isset($params['assign'])  ? $params['assign']  : null;

    $result = UserUtil::getTheme();

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}

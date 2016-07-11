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
 * Zikula_View function to determine whether the current user is logged in.
 *
 * This function will return true if that is true and false otherwise
 *
 * available parameters:
 *  - assign      if set, the loggedin status will be assigned to this variable
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return   bool   the logged in status
 */
function smarty_function_userloggedin($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $return = UserUtil::isLoggedIn();

    if ($assign) {
        $view->assign($assign, $return);
    } else {
        return $return;
    }
}

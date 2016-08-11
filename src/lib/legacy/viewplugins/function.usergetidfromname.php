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
 * Zikula_View function to get the user id for a given user.
 *
 * This function will return the user ID for a given username.
 *
 * available parameters:
 *  - uname       the username return the id for
 *  - assign      if set, the language will be assigned to this variable
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string The user ID
 */
function smarty_function_usergetidfromname($params, Zikula_View $view)
{
    $assign  = isset($params['assign']) ? $params['assign'] : null;
    $uname   = isset($params['uname']) ? $params['uname'] : null;

    if (!$uname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['usergetidfromname', 'uname']));

        return false;
    }

    $return = UserUtil::getIdFromName($uname);

    if ($assign) {
        $view->assign($assign, $return);
    } else {
        return $return;
    }
}

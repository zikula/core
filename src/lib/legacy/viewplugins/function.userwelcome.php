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
 * Zikula_View function to display the welcome message
 *
 * Example
 * {userwelcome}
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @see    function.userwelcome.php::smarty_function_userwelcome()
 *
 * @return string The welcome message
 */
function smarty_function_userwelcome($params, Zikula_View $view)
{
    if (UserUtil::isLoggedIn()) {
        $username = UserUtil::getVar('uname');
    } else {
        $username = __('anonymous guest');
    }

    return __f('Welcome, %s!', $username);
}

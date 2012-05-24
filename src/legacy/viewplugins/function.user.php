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
 * Zikula_View function to display the user name
 *
 * Example
 * {user}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    function.userwelcome.php::smarty_function_user()
 *
 * @return string The username.
 */
function smarty_function_user($params, Zikula_View $view)
{
    if (UserUtil::isLoggedIn()) {
        $username = UserUtil::getVar('uname');
    } else {
        $username = __('anonymous guest');
    }

    return DataUtil::formatForDisplayHTML($username);
}

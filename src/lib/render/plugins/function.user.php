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
 * Smarty function to display the user name
 *
 * Example
 * <!--[user]-->
 *
 * @see          function.userwelcome.php::smarty_function_user()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the username
 */
function smarty_function_user($params, &$smarty)
{
    if (UserUtil::isLoggedIn()) {
        $username = pnUserGetVar('uname');
    } else {
        $username = __('anonymous guest');
    }

    return DataUtil::formatForDisplayHTML($username);
}

<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display the welcome message
 *
 * Example
 * <!--[userwelcome]-->
 *
 * @see          function.userwelcome.php::smarty_function_userwelcome()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the welcome message
 */
function smarty_function_userwelcome($params, &$smarty)
{
    if (pnUserLoggedIn()) {
        $username = pnUserGetVar('uname');
    } else {
        $username = __('anonymous guest');
    }

    return __f('Welcome, %s!', $username);
}

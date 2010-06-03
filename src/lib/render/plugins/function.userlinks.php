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
 * Smarty function to display some user links
 *
 * Example
 * {userlinks start="[" end="]" seperator="|"}
 *
 *
 * @see          function.userlinks.php::smarty_function_userlinks()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $start       start delimiter
 * @param        string      $end         end delimiter
 * @param        string      $seperator   seperator
 * @return       string      user links
 */
function smarty_function_userlinks($params, &$smarty)
{
    $start     = isset($params['start'])     ? $params['start']    : '[';
    $end       = isset($params['end'])       ? $params['end']      : ']';
    $seperator = isset($params['seperator']) ? $params['seperator']: '|';

    if (UserUtil::isLoggedIn()) {
        $links = "$start ";
        $profileModule = System::getVar('profilemodule', '');
        if (!empty($profileModule) && ModUtil::available($profileModule)) {
            $links .= "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url($profileModule)) . '">' . __('Your Account') . "</a> $seperator ";
        }
        $links .= "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url('Users', 'user', 'logout')) . '">'  . __('Log out') . "</a> $end";

    } else {
        $links = "$start <a href=\"" . DataUtil::formatForDisplay(ModUtil::url('Users', 'user', 'register')) . '">' . __('Register new account') . "</a> $seperator "
               . "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url('Users', 'user', 'loginscreen')) . '">' . __('Login') . "</a> $end";
    }

    return DataUtil::formatForDisplayHTML($links);
}

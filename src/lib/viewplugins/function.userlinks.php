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
 * Zikula_View function to display some user links
 *
 * Example
 * {userlinks start="[" end="]" seperator="|"}
 *
 * Parameters:
 *  start     Start delimiter
 *  end       End delimiter
 *  seperator Seperator
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    function.userlinks.php::smarty_function_userlinks()
 *
 * @return string User links.
 */
function smarty_function_userlinks($params, Zikula_View $view)
{
    $start     = isset($params['start'])     ? $params['start']    : '[';
    $end       = isset($params['end'])       ? $params['end']      : ']';
    $seperator = isset($params['seperator']) ? $params['seperator']: '|';

    if (UserUtil::isLoggedIn()) {
        $links = "$start ";
        $profileModule = System::getVar('profilemodule', '');
        if (!empty($profileModule) && ModUtil::available($profileModule)) {
            $links .= "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url($profileModule, 'user', 'view')) . '">' . __('Your Account') . "</a> $seperator ";
        } else {
            $links .= "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url('Users', 'user', 'main')) . '">' . __('Your Account') . "</a> $seperator ";
        }
        $links .= "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url('Users', 'user', 'logout')) . '">'  . __('Log out') . "</a> $end";

    } else {
        $links = "$start <a href=\"" . DataUtil::formatForDisplay(ModUtil::url('Users', 'user', 'register')) . '">' . __('Register new account') . "</a> $seperator "
               . "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url('Users', 'user', 'login')) . '">' . __('Login') . "</a> $end";
    }

    return DataUtil::formatForDisplayHTML($links);
}

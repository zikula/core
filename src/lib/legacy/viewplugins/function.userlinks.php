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
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @see    function.userlinks.php::smarty_function_userlinks()
 *
 * @return string User links
 */
function smarty_function_userlinks($params, Zikula_View $view)
{
    $start     = isset($params['start']) ? $params['start'] : '[';
    $end       = isset($params['end']) ? $params['end'] : ']';
    $seperator = isset($params['seperator']) ? $params['seperator'] : '|';

    if (UserUtil::isLoggedIn()) {
        $links = "$start ";
        $profileModule = System::getVar('profilemodule', '');
        if (!empty($profileModule) && ModUtil::available($profileModule)) {
            $links .= "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url($profileModule, 'user', 'view')) . '">' . __('Your Account') . "</a> $seperator ";
        } else {
            $links .= "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url('ZikulaUsersModule', 'user', 'index')) . '">' . __('Your Account') . "</a> $seperator ";
        }
        $links .= "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url('ZikulaUsersModule', 'user', 'logout')) . '">'  . __('Log out') . "</a> $end";
    } else {
        $links = "$start <a href=\"" . DataUtil::formatForDisplay(ModUtil::url('ZikulaUsersModule', 'user', 'register')) . '">' . __('Register new account') . "</a> $seperator "
               . "<a href=\"" . DataUtil::formatForDisplay(ModUtil::url('ZikulaUsersModule', 'user', 'login')) . '">' . __('Login') . "</a> $end";
    }

    return DataUtil::formatForDisplayHTML($links);
}

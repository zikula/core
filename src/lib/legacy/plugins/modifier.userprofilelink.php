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
 * Zikula_View modifier to create a link to a users profile
 *
 * Example
 *
 *   Simple version, shows $username
 *   {$username|userprofilelink}
 *   Simple version, shows $username, using class="classname"
 *   {$username|userprofilelink:classname}
 *   Using profile.gif instead of username, no class
 *   {$username|userprofilelink:'':'images/profile.gif'}
 *
 *   Using language depending image from pnimg. Note that we pass
 *   the pnimg result array to the modifier as-is
 *   { pnimg src='profile.gif' assign=profile}
 *   {$username|userprofilelink:'classname':$profile}
 *
 * @param string  $string    The users name.
 * @param string  $class     The class name for the link (optional).
 * @param mixed   $image     The image to show instead of the username (optional).
 *                              May be an array as created by pnimg.
 * @param integer $maxLength If set then user names are truncated to x chars.
 *
 * @return string The output.
 */
function smarty_modifier_userprofilelink($string, $class = '', $image = '', $maxLength = 0)
{
    LogUtil::log(__f('Warning! Template modifier {$var|%1$s} is deprecated, please use {$var|%2$s} instead.', array('userprofilelink', 'profilelinkbyuname} {$var|profilelinkbyuid')), E_USER_DEPRECATED);

    // TODO - This does not handle cases where the uname is made up entirely of digits (e.g. $uname == "123456"). It will interpret it
    // as a uid. A new modifier is needed that acts on uids and only uids, and this modifier should act on unames and only unames.
    if (is_numeric($string)) {
        $uid = DataUtil::formatForStore($string);
        $uname = UserUtil::getVar('uname', $uid);
    } else {
        $uname = DataUtil::formatForStore($string);
        $uid = UserUtil::getIdFromName($uname);
    }

    $showUname = DataUtil::formatForDisplay($uname);

    $profileModule = System::getVar('profilemodule', '');

    if (isset($uid) && $uid && isset($uname) && $uname && ($uid > 1) && !empty($profileModule) && ModUtil::available($profileModule)
            && (strtolower($uname) <> strtolower(ModUtil::getVar(Users_Constant::MODNAME, Users_Constant::MODVAR_ANONYMOUS_DISPLAY_NAME)))) {
        if (!empty($class)) {
            $class = ' class="' . DataUtil::formatForDisplay($class) . '"';
        }

        if (!empty($image)) {
            if (is_array($image)) {
                // if it is an array we assume that it is an pnimg array
                $show = '<img src="' . DataUtil::formatForDisplay($image['src']) . '" alt="' . DataUtil::formatForDisplay($image['alt']) . '" width="' . DataUtil::formatForDisplay($image['width']) . '" height="' . DataUtil::formatForDisplay($image['height']) . '" />';
            } else {
                $show = '<img src="' . DataUtil::formatForDisplay($image) . '" alt="' . $showUname . '" />';
            }
        } elseif ($maxLength > 0) {
            // truncate the user name to $maxLength chars
            $showLength = strlen($showUname);
            $truncEnd = ($maxLength > $showLength) ? $showLength : $maxLength;
            $showUname = substr($string, 0, $truncEnd);
        }

        $profileLink = '<a' . $class . ' title="' . DataUtil::formatForDisplay(__('Personal information')) . ': ' . $showUname . '" href="' . DataUtil::formatForDisplay(ModUtil::url($profileModule, 'user', 'view', array('uid' => $uid), null, null, true)) . '">' . $showUname . '</a>';
    } elseif (!empty($image)) {
        $profileLink = ''; //image for anonymous user should be "empty"
    } else {
        $profileLink = DataUtil::formatForDisplay($string);
    }

    return $profileLink;
}


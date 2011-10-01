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
 * Zikula_View modifier to create a link to a users profile from the username.
 *
 * Example
 *
 *   Simple version, shows $username
 *   {$username|profilelinkbyuname}
 *   Simple version, shows $username, using class="classname"
 *   {$username|profilelinkbyuname:classname}
 *   Using profile.gif instead of username, no class
 *   {$username|profilelinkbyuname:'':'images/profile.gif'}
 *
 *   Using language depending image from pnimg. Note that we pass
 *   the pnimg result array to the modifier as-is
 *   {img src='profile.gif' assign=profile}
 *   {$username|profilelinkbyuname:'classname':$profile}
 *
 * @param string  $string    The users name.
 * @param string  $class     The class name for the link (optional).
 * @param mixed   $image     The image to show instead of the username (optional).
 *                              May be an array as created by pnimg.
 * @param integer $maxLength If set then user names are truncated to x chars.
 *
 * @return string The output.
 */
function smarty_modifier_profilelinkbyuname($uname, $class = '', $image = '', $maxLength = 0)
{
    if (empty($uname)) {
        return $uname;
    }

    $uid        = UserUtil::getIdFromName($uname);
    $showUname  = DataUtil::formatForDisplay($uname);

    $profileModule = System::getVar('profilemodule', '');

    if ($uid && ($uid > 1) && !empty($profileModule) && ModUtil::available($profileModule)) {
        if (!empty($class)) {
            $class = ' class="' . DataUtil::formatForDisplay($class) . '"';
        }

        if (!empty($image)) {
            if (is_array($image)) {
                // if it is an array we assume that it is an img array
                $show = '<img src="' . DataUtil::formatForDisplay($image['src']) . '" alt="' . DataUtil::formatForDisplay($image['alt']) . '" width="' . DataUtil::formatForDisplay($image['width']) . '" height="' . DataUtil::formatForDisplay($image['height']) . '" />';
            } else {
                $show = '<img src="' . DataUtil::formatForDisplay($image) . '" alt="' . $showUname . '" />';
            }
        } elseif ($maxLength > 0) {
            // truncate the user name to $maxLength chars
            $length     = strlen($uname);
            $truncEnd   = ($maxLength > $length) ? $length : $maxLength;
            $showUname  = DataUtil::formatForDisplay(substr($uname, 0, $truncEnd));
            $show       = $showUname;
        } else {
            $show = $showUname;
        }

        $profileLink = '<a' . $class . ' title="' . DataUtil::formatForDisplay(__('Personal information')) . ': ' . $showUname . '" href="' . DataUtil::formatForDisplay(ModUtil::url($profileModule, 'user', 'view', array('uid' => $uid), null, null, true)) . '">' . $show . '</a>';
    } elseif (!empty($image)) {
        $profileLink = ''; // image for anonymous user should be "empty"
    } else {
        $profileLink = $showUname;
    }

    return $profileLink;
}

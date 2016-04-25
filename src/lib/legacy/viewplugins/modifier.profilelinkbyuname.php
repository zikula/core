<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

    $uid = UserUtil::getIdFromName($uname);

    $profileModule = System::getVar('profilemodule', '');

    if ($uid && ($uid > 1) && !empty($profileModule) && ModUtil::available($profileModule)) {
        $userDisplayName = ModUtil::apiFunc($profileModule, 'user', 'getUserDisplayName', array('uid' => $uid));

        if (empty($userDisplayName)) {
            $userDisplayName = $uname;
        }

        if (!empty($class)) {
            $class = ' class="' . DataUtil::formatForDisplay($class) . '"';
        }

        if (!empty($image)) {
            if (is_array($image)) {
                // if it is an array we assume that it is an img array
                $show = '<img src="' . DataUtil::formatForDisplay($image['src']) . '" alt="' . DataUtil::formatForDisplay($image['alt']) . '" width="' . DataUtil::formatForDisplay($image['width']) . '" height="' . DataUtil::formatForDisplay($image['height']) . '" />';
            } else {
                $show = '<img src="' . DataUtil::formatForDisplay($image) . '" alt="' . DataUtil::formatForDisplay($userDisplayName) . '" />';
            }
        } elseif ($maxLength > 0) {
            // truncate the user name to $maxLength chars
            $length     = strlen($userDisplayName);
            $truncEnd   = ($maxLength > $length) ? $length : $maxLength;
            $show  = DataUtil::formatForDisplay(substr($userDisplayName, 0, $truncEnd));
        } else {
            $show = DataUtil::formatForDisplay($userDisplayName);
        }

        $profileLink = '<a' . $class . ' title="' . DataUtil::formatForDisplay(__('Profile')) . ': ' . DataUtil::formatForDisplay($userDisplayName) . '" href="' . DataUtil::formatForDisplay(ModUtil::url($profileModule, 'user', 'view', array('uid' => $uid), null, null, true)) . '">' . $show . '</a>';
    } elseif (!empty($image)) {
        $profileLink = ''; // image for anonymous user should be "empty"
    } else {
        $profileLink = DataUtil::formatForDisplay($uname);
    }

    return $profileLink;
}

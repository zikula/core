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
 * Smarty modifier to create a link to a users profile
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
 *                            May be an array as created by pnimg.
 * @param integer $maxLength If set then user names are truncated to x chars.
 *
 * @return string The output.
 */
function smarty_modifier_userprofilelink($string, $class = '', $image = '', $maxLength = 0)
{
    $uname = $uid = $string = DataUtil::formatForDisplay($string);

    if (!is_numeric($string)) {
        $uid = UserUtil::getIdFromName($string);
    }

    $profileModule = System::getVar('profilemodule', '');

    if ($uid <> false && $uid > 1 && !empty($profileModule) && ModUtil::available($profileModule) && strtolower($string) <> strtolower(ModUtil::getVar('Users', 'anonymous'))) {
        if (!empty($class)) {
            $class = ' class="' . DataUtil::formatForDisplay($class) . '"';
        }

        if (!empty($image)) {
            if (is_array($image)) {
                // if it is an array we assume that it is an pnimg array
                $show = '<img src="' . DataUtil::formatForDisplay($image['src']) . '" alt="' . DataUtil::formatForDisplay($image['alt']) . '" width="' . DataUtil::formatForDisplay($image['width']) . '" height="' . DataUtil::formatForDisplay($image['height']) . '" />';
            } else {
                $show = '<img src="' . DataUtil::formatForDisplay($image) . '" alt="' . $string . '" />';
            }
        } elseif ($maxLength > 0) {
            // truncate the user name to $maxLength chars
            $showLength = strlen($string);
            $truncEnd = ($maxLength > $showLength) ? $showLength : $maxLength;
            $show = substr($string, 0, $truncEnd);

        } elseif (is_numeric($string)) {
            $show = $uname = UserUtil::getVar('uname', $uid);
        } else {
            $show = $string;
        }

        if (!is_numeric($string)) {
            $string = '<a' . $class . ' title="' . DataUtil::formatForDisplay(__('Personal information')) . ': ' . $string . '" href="' . DataUtil::formatForDisplay(ModUtil::url($profileModule, 'user', 'view', array('uname' => $string), null, null, true)) . '">' . $show . '</a>';
        } else {
            $string = '<a' . $class . ' title="' . DataUtil::formatForDisplay(__('Personal information')) . ': ' . $uname . '" href="' . DataUtil::formatForDisplay(ModUtil::url($profileModule, 'user', 'view', array('uid' => $uid), null, null, true)) . '">' . $show . '</a>';
        }
    } elseif (!empty($image)) {
        $string = ''; //image for anonymous user should be "empty"
    } elseif (is_numeric($string)) {
        $string = UserUtil::getVar('uname', $uid);
    }

    return $string;
}


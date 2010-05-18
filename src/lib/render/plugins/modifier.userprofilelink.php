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
 * Smarty modifier to create a link to a users profile
 *
 * Example
 *
 *   Simple version, shows $username
 *   <!--[$username|userprofilelink]-->
 *   Simple version, shows $username, using class="classname"
 *   <!--[$username|userprofilelink:classname]-->
 *   Using profile.gif instead of username, no class
 *   <!--[$username|userprofilelink:'':'images/profile.gif']-->
 *
 *   Using language depending image from pnimg. Note that we pass
 *   the pnimg result array to the modifier as-is
 *   <!--[ pnimg src='profile.gif' assign=profile]-->
 *   <!--[$username|userprofilelink:'classname':$profile]-->
 *
 * @param        $string    string       the users name
 * @param        $class     string       the class name for the link (optional)
 * @param        $image     string/array the image to show instead of the username (optional)
 *                                       may be an array as created by pnimg
 * @param        $maxLength integer      if set then user names are truncated to x chars
 * @return       string   the output
 */
function smarty_modifier_userprofilelink($string, $class = '', $image = '', $maxLength = 0)
{
    $uname = $uid = $string = DataUtil::formatForDisplay($string);

    if (!is_numeric($string)) {
        $uid = pnUserGetIDFromName($string);
    }

    $profileModule = pnConfigGetVar('profilemodule', '');

    if ($uid <> false && $uid > 1 && !empty($profileModule) && pnModAvailable($profileModule) && strtolower($string) <> strtolower(ModUtil::getVar('Users', 'anonymous'))) {
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
            $show = $uname = pnUserGetVar('uname', $uid);
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
    }

    return $string;
}


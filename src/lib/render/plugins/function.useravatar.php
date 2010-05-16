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
 * Smarty function to display the avatar of a user
 *
 * Example
 * <!--[useravatar uid="2"]-->
 *
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        integer     $uid         user-id
 * @return       string      a formatted string containing the avatar image
 */

function smarty_function_useravatar($params, &$smarty)
{
    if (!isset($params['uid'])) {
        $smarty->trigger_error("Error! Missing 'uid' attribute for useravatar.");
        return false;
    }

    $email           = pnUserGetVar('email', $params['uid']);
    $avatar          = pnUserGetVar('avatar', $params['uid']);
    $uname           = pnUserGetVar('uname', $params['uid']);
    $avatarpath      = pnModGetVar('Users', 'avatarpath', 'images/avatar');
    $allowgravatars  = pnModGetVar('Users', 'allowgravatars', 1);
    $gravatarimage   = pnModGetVar('Users', 'gravatarimage', 'gravatar.gif');

    if (isset($avatar) && !empty($avatar) && $avatar != $gravatarimage && $avatar != 'blank.gif') {
        $avatarURL = pnGetBaseURL() . $avatarpath . '/' . $avatar;
    } else if (($avatar == $gravatarimage) && ($allowgravatars == 1)) {
        if (!isset($params['rating'])) $params['rating'] = false;
        if (!isset($params['size'])) $params['size'] = 80;

        $avatarURL = 'http://www.gravatar.com/avatar.php?gravatar_id=' . md5($email);
        if (isset($params['rating']) && !empty($params['rating'])) $avatarURL .= "&rating=".$params['rating'];
        if (isset($params['size']) && !empty($params['size'])) $avatarURL .="&size=".$params['size'];
        $avatarURL .= "&default=".urlencode(pnGetBaseURL() . $avatarpath . '/' . $gravatarimage);
    } else { // e.g. blank.gif or empty avatars
        return false;
    }

    $classString = '';
    if (isset($params['class'])) {
        $classString = "class=\"$params[class]\" ";
    }

    $html = '<img ' . $classString . ' src="' . DataUtil::formatForDisplay($avatarURL) . '" title="' . DataUtil::formatForDisplay($uname) . '" alt="' . DataUtil::formatForDisplay($uname) . '" />';

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $avatarURL);
    } else {
        return $html;
    }

}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\UsersModule\Constant as UsersConstant;

/**
 * Zikula_View function to display the avatar of a user
 *
 * Available parameters:
 *   - uid            User uid
 *   - width, height  Width and heigt of the image (optional)
 *   - assign         The results are assigned to the corresponding variable instead of printed out (optional).
 * Gravatar parameters
 *   - size           Size of the gravtar (optional)
 *   - rating         Gravatar allows users to self-rate their images so that they can indicate if an image is appropriate for a certain audience.
 *                    [g|pg|r|x] see: http://en.gravatar.com/site/implement/images/ (optional)
 *
 * Examples:
 * {useravatar uid="2"}
 * {useravatar uid="2" width=80 height=80}
 * {useravatar uid="2" size=80 rating=g}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string A formatted string containing the avatar image.
 */

function smarty_function_useravatar($params, Zikula_View $view)
{
    if (!isset($params['uid'])) {
        $view->trigger_error("Error! Missing 'uid' attribute for useravatar.");

        return false;
    }

    $email           = UserUtil::getVar('email', $params['uid']);
    $gravatarimage   = ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_GRAVATAR_IMAGE, UsersConstant::DEFAULT_GRAVATAR_IMAGE);
    $avatar          = UserUtil::getVar('avatar', $params['uid'], $gravatarimage);
    $uname           = UserUtil::getVar('uname', $params['uid']);
    $avatarpath      = ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_AVATAR_IMAGE_PATH, UsersConstant::DEFAULT_AVATAR_IMAGE_PATH);
    $allowgravatars  = ModUtil::getVar(UsersConstant::MODNAME, UsersConstant::MODVAR_GRAVATARS_ENABLED, UsersConstant::DEFAULT_GRAVATARS_ENABLED);

    if (isset($avatar) && !empty($avatar) && $avatar != $gravatarimage && $avatar != 'blank.gif') {
        $avatarURL = System::getBaseUrl() . $avatarpath . '/' . $avatar;
    } elseif (($avatar == $gravatarimage) && ($allowgravatars == 1)) {
        if (!isset($params['rating'])) {
            $params['rating'] = false;
        }
        if (!isset($params['size'])) {
            if (isset($params['width']) || isset($params['height'])) {
                if ((isset($params['width']) && !isset($params['height'])) || (isset($params['width']) && isset($params['height']) && $params['width'] < $params['size'])) {
                    $params['size'] = $params['width'];
                } elseif ((!isset($params['width']) && isset($params['height'])) || (isset($params['width']) && isset($params['height']) && $params['width'] > $params['size'])) {
                    $params['size'] = $params['height'];
                } else {
                    $params['size'] = 80;
                }
            } else {
                $params['size'] = 80;
            }
        }
        $params['width']  = $params['size'];
        $params['height'] = $params['size'];

        $avatarURL = 'http://www.gravatar.com/avatar.php?gravatar_id=' . md5($email);
        if (isset($params['rating']) && !empty($params['rating'])) {
            $avatarURL .= "&rating=".$params['rating'];
        }
        if (isset($params['size']) && !empty($params['size'])) {
            $avatarURL .= "&size=".$params['size'];
        }
        $avatarURL .= "&default=".urlencode(System::getBaseUrl() . $avatarpath . '/' . $gravatarimage);
    } else {
        // e.g. blank.gif or empty avatars
        return false;
    }

    $classString = '';
    if (isset($params['class'])) {
        $classString = "class=\"$params[class]\" ";
    }

    $html = '<img ' . $classString . ' src="' . DataUtil::formatForDisplay($avatarURL) . '" title="' . DataUtil::formatForDisplay($uname) . '" alt="' . DataUtil::formatForDisplay($uname) . '"';
    if (isset($params['width'])) {
        $html .= ' width="'.$params['width'].'"';
    }
    if (isset($params['height'])) {
        $html .= ' height="'.$params['height'].'"';
    }
    $html .= ' />';

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $avatarURL);
    } else {
        return $html;
    }
}

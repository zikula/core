<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Api;

use UserUtil;
use ModUtil;
use Zikula\UsersModule\Constant as UsersConstant;
use ZLanguage;
use System;

/**
 * The Account API provides links for modules on the "user account page"; this class provides them for the Users module.
 */
class AccountApi extends \Zikula_AbstractApi
{
    /**
     * Return an array of items to show in the the user's account panel.
     *
     * @param mixed $args Not used.
     *
     * @return array Indexed array of items.
     */
    public function getAll($args)
    {
        $items = array();

        // Show change password action only if the account record contains a password, and the password is not the
        // special marker for an account created without a Users module authentication password.
        $pass = UserUtil::getVar('pass');
        if (!empty($pass) && ($pass != UsersConstant::PWD_NO_USERS_AUTHENTICATION)) {
            // show edit password link
            $items['1'] = array(
                'url'   => $this->get('router')->generate('zikulausersmodule_user_changepassword'),
                'module' => $this->name,
                'title' => $this->__('Password changer'),
                'icon'  => 'password.png'
            );
        }

        // show edit email link if configured to manage email address
        if ($this->getVar('changeemail', true)) {
            $items['2'] = array(
                'url'   => $this->get('router')->generate('zikulausersmodule_user_changeemail'),
                'module' => $this->name,
                'title' => $this->__('E-mail address manager'),
                'icon'  => 'message.png'
            );
        }

        // check if the users block exists
        $blocks = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getall');
        $usersModuleID = ModUtil::getIdFromName($this->name);
        $found = false;
        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                if (($block['mid'] == $usersModuleID) && ($block['bkey'] == 'user')) {
                    $found = true;
                    break;
                }
            }
        }

        if ($found) {
            $items['3'] = array(
                'url'   => $this->get('router')->generate('zikulausersmodule_user_usersblock'),
                'module' => $this->name,
                'title' => $this->__('Personal custom block'),
                'icon'  => 'folder_home.png'
            );
        }

        if (System::getVar('multilingual')) {
            if (count(ZLanguage::getInstalledLanguages()) > 1) {
                $items['4'] = array(
                    'url'   => $this->get('router')->generate('zikulausersmodule_user_changelang'),
                    'module' => $this->name,
                    'title' => $this->__('Language switcher'),
                    'icon'  => 'locale.png'
                );
            }
        }

        $items['5'] = array(
            'url'   => $this->get('router')->generate('zikulausersmodule_user_logout'),
            'module' => $this->name,
            'title' => $this->__('Log out'),
            'icon'  => 'exit.png'
        );

        // Return the items
        return $items;
    }
}

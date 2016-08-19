<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * The Account API provides links for modules on the "user account page"; this class provides them for the Users module.
 */
class Users_Api_Account extends Zikula_AbstractApi
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
        if (!empty($pass) && ($pass != Users_Constant::PWD_NO_USERS_AUTHENTICATION)) {
            // show edit password link
            $items['1'] = array(
                'url'   => ModUtil::url($this->name, 'user', 'changePassword'),
                'module'=> $this->name,
                'title' => $this->__('Password changer'),
                'icon'  => 'password.png'
            );
        }

        // show edit email link if configured to manage email address
        if ($this->getVar('changeemail', true)) {
            $items['2'] = array(
                'url'   => ModUtil::url($this->name, 'user', 'changeEmail'),
                'module'=> $this->name,
                'title' => $this->__('E-mail address manager'),
                'icon'  => 'message.png'
            );
        }

        // check if the users block exists
        $blocks = ModUtil::apiFunc('Blocks', 'user', 'getall');
        $usersModuleID = ModUtil::getIdFromName($this->name);
        $found = false;
        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                if (($block['mid'] == $usersModuleID) && ($block['bkey'] == 'User')) {
                    $found = true;
                    break;
                }
            }
        }

        if ($found) {
            $items['3'] = array(
                'url'   => ModUtil::url($this->name, 'user', 'usersBlock'),
                'module'=> $this->name,
                'title' => $this->__('Personal custom block'),
                'icon'  => 'folder_home.png'
            );
        }

        if (System::getVar('multilingual')) {
            if (count(ZLanguage::getInstalledLanguages()) > 1) {
                $items['4'] = array(
                    'url'   => ModUtil::url($this->name, 'user', 'changeLang'),
                    'module'=> $this->name,
                    'title' => $this->__('Language switcher'),
                    'icon'  => 'locale.png'
                );
            }
        }

        $items['5'] = array(
            'url'   => ModUtil::url($this->name, 'user', 'logout'),
            'module'=> $this->name,
            'title' => $this->__('Log out'),
            'icon'  => 'exit.png'
        );

        // Return the items
        return $items;
    }
}

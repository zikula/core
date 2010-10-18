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
class Users_Api_Account extends Zikula_Api
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

        $modvars = $this->getVars();

        if ($modvars['changepassword'] == 1) {
            // show edit password link
            $items['1'] = array('url' => ModUtil::url('Users', 'user', 'changePassword'),
                                'module' => 'Users',
                                'title' => $this->__('Password changer'),
                                'icon' => 'password.png');
        }

        if ($modvars['changeemail'] == 1) {
            // show edit email link
            $items['2'] = array('url' => ModUtil::url('Users', 'user', 'changeEmail'),
                                'module' => 'Users',
                                'title' => $this->__('E-mail address manager'),
                                'icon' => 'message.png');
        }

        // check if the users block exists
        $blocks = ModUtil::apiFunc('Blocks', 'user', 'getAll');
        $mid = ModUtil::getIdFromName('Users');
        $found = false;
        foreach ($blocks as $block) {
            if ($block['mid'] == $mid && $block['bkey'] == 'user') {
                $found = true;
                break;
            }
        }

        if ($found) {
            $items['3'] = array('url' => ModUtil::url('Users', 'user', 'usersBlock'),
                                'module' => 'Users',
                                'title' => $this->__('Personal custom block'),
                                'icon' => 'folder_home.png');
        }

        if (System::getVar('multilingual')) {
            if (count(ZLanguage::getInstalledLanguages()) > 1) {
                $items['4'] = array('url' => ModUtil::url('Users', 'user', 'changeLang'),
                                'module' => 'Users',
                                'title' => $this->__('Language switcher'),
                                'icon' => 'locale.png');
            }
        }

        $items['5'] = array('url' => ModUtil::url('Users', 'user', 'logout'),
                            'module' => 'Users',
                            'title' => $this->__('Log out'),
                            'icon' => 'exit.png');

        // Return the items
        return $items;
    }
}

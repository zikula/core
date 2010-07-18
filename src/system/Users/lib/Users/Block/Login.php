<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * A block that allows users to log into the system.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Block_Login extends Zikula_Block
{
    /**
     * return the block info
    */
    public function info()
    {
        return array(
        'module'         => 'Users',
        'text_type'      => $this->__('Log-in'),
        'text_type_long' => $this->__('Log-in block'),
        'allow_multiple' => false,
        'form_content'   => false,
        'form_refresh'   => false,
        'show_preview'   => false
        );
    }

    /**
     * initialise the block
     *
     * Adds the blocks security schema to the PN environment
    */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Loginblock::', 'Block title::');
    }

    /**
     * Display the block
     *
     * Display the output of the login block
    */
    public function display($row)
    {
        if (!SecurityUtil::checkPermission('Loginblock::', $row['title'].'::', ACCESS_READ)) {
            return;
        }

        if (!UserUtil::isLoggedIn()) {
            if (empty($row['title'])) {
                $row['title'] = DataUtil::formatForDisplay('Login');
            }

            $authmodules = array();
            $modules = ModUtil::getModulesCapableOf('authentication');
            foreach ($modules as $modinfo) {
                if (ModUtil::available($modinfo['name'])) {
                    $authmodules[$modinfo['name']] = $modinfo;
                }
            }

            $authmodule = FormUtil::getPassedValue('loginwith', $this->getVar('default_authmodule', 'Users'), 'GET');

            $this->view->assign('default_authmodule', $this->getVar('default_authmodule', 'Users'))
                           ->assign('authmodule', $authmodule)
                           ->assign('authmodules', $authmodules)
                           ->assign('seclevel', System::getVar('seclevel'))
                           ->assign('allowregistration', $this->getVar('reg_allowreg'))
                           ->assign('returnurl', System::getCurrentUri());

            $row['content'] = $this->view->fetch('users_block_login.tpl');

            return BlockUtil::themeBlock($row);
        }

        return;
    }
}

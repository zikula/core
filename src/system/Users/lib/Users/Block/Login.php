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
 * A block that allows users to log into the system.
 */
class Users_Block_Login extends Zikula_Block
{
    /**
     * Initialise the block.
     *
     * Adds the blocks security schema to the PN environment.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Loginblock::', 'Block title::');
    }

    /**
     * Return the block info.
     *
     * @return array A blockinfo structure.
     */
    public function info()
    {
        return array('module'         => 'Users',
                     'text_type'      => $this->__('Log-in'),
                     'text_type_long' => $this->__('Log-in block'),
                     'allow_multiple' => false,
                     'form_content'   => false,
                     'form_refresh'   => false,
                     'show_preview'   => false);
    }

    /**
     * Display the output of the login block.
     *
     * @param array $blockInfo A blockinfo structure.
     *
     * @return string|void The output.
     */
    public function display($blockInfo)
    {
        if (!SecurityUtil::checkPermission('Loginblock::', $blockInfo['title'].'::', ACCESS_READ)) {
            return;
        }

        if (!UserUtil::isLoggedIn()) {
            if (empty($blockInfo['title'])) {
                $blockInfo['title'] = DataUtil::formatForDisplay('Login');
            }

            $authmodules = array();
            $modules = ModUtil::getModulesCapableOf('authentication');
            foreach ($modules as $modinfo) {
                if (ModUtil::available($modinfo['name'])) {
                    $authmodules[$modinfo['name']] = $modinfo;
                }
            }

            $authmodule = FormUtil::getPassedValue('loginwith', $this->getVar('default_authmodule', 'Users'), 'GET');

            $this->view->setCaching(false);
            $this->view->assign('default_authmodule', $this->getVar('default_authmodule', 'Users'))
                       ->assign('authmodule', $authmodule)
                       ->assign('authmodules', $authmodules)
                       ->assign('seclevel', System::getVar('seclevel'))
                       ->assign('allowregistration', $this->getVar('reg_allowreg'))
                       ->assign('returnurl', System::getCurrentUri());

            $blockInfo['content'] = $this->view->fetch('users_block_login.tpl');

            return BlockUtil::themeBlock($blockInfo);
        }

        return;
    }
}

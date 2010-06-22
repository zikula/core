<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
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
            // we don't need a cached id since the content of this block will always
            // be the same
            // check out if the contents are cached.
            // If this is the case, we do not need to make DB queries.
            if ($this->renderer->is_cached('users_block_login.htm')) {
                $row['content'] = $this->renderer->fetch('users_block_login.htm');
                return BlockUtil::themeBlock($row);
            }

            if (empty($row['title'])) {
                $row['title'] = DataUtil::formatForDisplay('Login');
            }

            $this->renderer->assign('default_authmodule', ModUtil::getVar('Users', 'default_authmodule', 'Users'))
                           ->assign('authmodule', ModUtil::getVar('Users', 'default_authmodule', 'Users'))
                           ->assign('authmodules', array(ModUtil::getInfo(ModUtil::getIdFromName('Users'))))
                           ->assign('seclevel', System::getVar('seclevel'))
                           ->assign('allowregistration', ModUtil::getVar('Users', 'reg_allowreg'))
                           ->assign('returnurl', System::getCurrentUri());

            // determine whether to show the rememberme option
            $this->renderer->assign('rememberme', System::getVar('seclevel'));
            
            $row['content'] = $this->renderer->fetch('users_block_login.htm');

            return BlockUtil::themeBlock($row);
        }

        return;
    }
}

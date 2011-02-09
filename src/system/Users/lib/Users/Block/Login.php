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
class Users_Block_Login extends Zikula_Controller_Block
{

    /**
     * Post-construction initialization.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // Set caching to false by default.
        $this->view->setCaching(false);
    }

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

            // If there is more than one authmodule available don't assume a default.
            $authmodule = false;
            $numAuthmodules = count($authmodules);
            if (!$numAuthmodules || ($numAuthmodules <= 0)) {
                // NOTE: as of commit [cd4edce22fc1c123a6fb4ffc5b60b05b56a472df 22-Jan-2011 02:51:46 UTC
                // (The 'mods list' should always contain the essential core modules refs #2709)]
                // this condition should be impossible, as the commit ensures that the Users module will
                // always respond to ModUtil::getModulesCapableOf('authentication') regardless of its
                // state. We're leaving the code here for now until we decide how to make logging in
                // with a uname/pwd optional. At some point someone will want to allow access to a site
                // using *only* an authmodule other than Users. For example, the site might want to
                // accept only authentication from OpenID, or might want to accept authentication only
                // from an internal corporate LDAP server. In those cases, we need to decide what to
                // do about (1) preventing users from accessing the default uname/pwd method, and (2)
                // ensuring that the admin user can *always* log in with a Users module uname/pwd in
                // cases where everything else is broken or unavailable. If we make it possible again
                // for the Users module to not be on the getModulesCapableOf('authentication') list,
                // then we'll need this again.
                //
                // If we go another way, then this specific 'if' condition can be removed (although
                // the others are still required).

                // No auth modules?! We know Users is installed, so force that one.
                $authmodules[] = ModUtil::getInfoFromName('Users');
                $authmodule = 'Users';

                // Also, log the situation.
                LogUtil::log('There were no modules capable of authentication. Forcing the Users module to be used for authentication.', Zikula_ErrorHandler::CRIT);
            } elseif ($numAuthmodules == 1) {
                // There is exactly one authmodule available, so use that as the default
                $authmodule = $modules[0]['name'];
            } else {
                // There is more than one authmodule available, get the one selected.
                // If there are none selected, then do not default.
                $authmodule = FormUtil::getPassedValue('loginwith', false, 'GETPOST');
            }

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

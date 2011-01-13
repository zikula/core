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
 * Provides metadata for this module to the Modules module.
 */
class Users_Version extends Zikula_Version
{
    /**
     * Assemble and return module metadata.
     *
     * @return array Module metadata.
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Users manager');
        $meta['description'] = $this->__('Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.');
        //! module name that appears in URL
        $meta['url']  = $this->__('users');
        $meta['capabilities'] = array();
        $meta['capabilities']['authentication'] = array('version' => '1.0');
        $meta['capabilities'][HookUtil::SUBSCRIBER_CAPABLE] = array('enabled' => true);

        // Be careful about version numbers. version_compare() is used to handle special situations.
        // 0.9 < 0.9.0 < 1 < 1.0 < 1.0.1 < 1.2 < 1.18 < 1.20 < 2.0 < 2.0.0 < 2.0.1
        // From this version forward, please use the major.minor.point format below.
        $meta['version'] = '2.1.3';
        $meta['securityschema'] = array('Users::' => 'Uname::User ID',
                                        'Users::MailUsers' => '::');

        return $meta;
    }

    protected function setupHookBundles()
    {
        $bundle = new Zikula_Version_HookSubscriberBundle('modulehook_area.users.user', $this->__('User Hooks'));
        $bundle->addType('ui.edit', 'users.hook.user.ui.edit'); // users_admin_newuser.tpl L#111 & users_user_register.tpl L#172 & user_admin_modify.tpl L#104
        $bundle->addType('ui.delete', 'users.hook.user.ui.delete'); // users_admin_deleteusers.tpl L#22
        $bundle->addType('validate.edit', 'users.hook.user.validate.edit'); // Users_Controller_Admin L#703 & Users_Controller_User L#272 & Users_Controller_Ajax L#115
        $bundle->addType('validate.delete', 'users.hook.user.validate.delete'); // Users_Controller_Admin L#882
        $bundle->addType('process.edit', 'users.hook.user.process.edit'); // Users_Api_Registration L#847 & Users_Api_Admin L#247
        $bundle->addType('process.delete', 'users.hook.user.process.delete'); // Users_Api_Admin L#328
        $this->registerHookSubscriberBundle($bundle);
    }
}
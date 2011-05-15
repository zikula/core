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
 * Provides metadata for this module to the Extensions module.
 */
class Users_Version extends Zikula_AbstractVersion
{
    /**
     * Assemble and return module metadata.
     *
     * @return array Module metadata.
     */
    public function getMetaData()
    {
        return array(
            // Be careful about version numbers. version_compare() is used to handle special situations.
            // 0.9 < 0.9.0 < 1 < 1.0 < 1.0.1 < 1.2 < 1.18 < 1.20 < 2.0 < 2.0.0 < 2.0.1
            // From this version forward, please use the major.minor.point format below.
            'version'       => '2.2.0',

            'displayname'   => $this->__('Users'),
            'description'   => $this->__('Provides an interface for configuring and administering registered user accounts. Incorporates all needed functionality, but can work in close unison with the third party profile module configured in the general settings of the site.'),

            // Module name that appears in URL
            'url'           => $this->__('users'),

            // Advertised capabilities
            'capabilities'  => array(
                Users_Constant::CAPABILITY_AUTHENTICATION => array('version' => '1.0'),
                HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true),
                HookUtil::PROVIDER_CAPABLE => array('enabled' => true),
            ),

            // Dependencies
            'core_min'      => '1.3.0',

            // Security Schema
            'securityschema'=> array(
                'Users::'           => 'Uname::User ID',
                'Users::MailUsers'  => '::',
            ),
        );
    }

    /**
     * Define the hook bundles supported by this module.
     *
     * @return void
     */
    protected function setupHookBundles()
    {
        // Subscriber bundles

        // Bundle for forms that create and edit user account records (both by admin and by user).
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber_area.ui.users.user', 'ui', $this->__('User and registration management hooks'));
        $bundle->addType('ui.view',         'users.hook.user.ui.view');
        $bundle->addType('ui.edit',         'users.hook.user.ui.edit');
        $bundle->addType('ui.delete',       'users.hook.user.ui.delete');
        $bundle->addType('validate.edit',   'users.hook.user.validate.edit');
        $bundle->addType('validate.delete', 'users.hook.user.validate.delete');
        $bundle->addType('process.edit',    'users.hook.user.process.edit');
        $bundle->addType('process.delete',  'users.hook.user.process.delete');
        $this->registerHookSubscriberBundle($bundle);

        // Bundle for the login form (both the block and the login).
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber_area.ui.users.login', 'ui', $this->__('Login form and block hooks'));
        $bundle->addType('ui.edit',         'users.hook.login.ui.edit');
        $bundle->addType('validate.edit',   'users.hook.login.validate.edit');
        $bundle->addType('process.edit',    'users.hook.login.process.edit');
        $this->registerHookSubscriberBundle($bundle);

        // Bundle for the list of authentication methods on the login block and the login.
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber_area.ui.users.authentication_method_selectors', 'ui', $this->__('Pre-login authentication method selector hooks'));
        $bundle->addType('ui.view',         'users.hook.authentication_method_selectors.ui.view');
        $this->registerHookSubscriberBundle($bundle);
    }
}
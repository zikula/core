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
                HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true)
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

        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.users.ui_hooks.user', 'ui_hooks', $this->__('User management hooks'));
        $bundle->addEvent('display_view',    'users.ui_hooks.user.display_view');

        $bundle->addEvent('form_edit',       'users.ui_hooks.user.form_edit');
        $bundle->addEvent('validate_edit',   'users.ui_hooks.user.validate_edit');
        $bundle->addEvent('process_edit',    'users.ui_hooks.user.process_edit');

        $bundle->addEvent('form_delete',     'users.ui_hooks.user.form_delete');
        $bundle->addEvent('validate_delete', 'users.ui_hooks.user.validate_delete');
        $bundle->addEvent('process_delete',  'users.ui_hooks.user.process_delete');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.users.ui_hooks.registration', 'ui_hooks', $this->__('Registration management hooks'));
        $bundle->addEvent('display_view',    'users.ui_hooks.registration.display_view');

        $bundle->addEvent('form_edit',       'users.ui_hooks.registration.form_edit');
        $bundle->addEvent('validate_edit',   'users.ui_hooks.registration.validate_edit');
        $bundle->addEvent('process_edit',    'users.ui_hooks.registration.process_edit');

        $bundle->addEvent('form_delete',     'users.ui_hooks.registration.form_delete');
        $bundle->addEvent('validate_delete', 'users.ui_hooks.registration.validate_delete');
        $bundle->addEvent('process_delete',  'users.ui_hooks.registration.process_delete');
        $this->registerHookSubscriberBundle($bundle);

        // Bundle for the login form
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.users.ui_hooks.login_screen', 'ui_hooks', $this->__('Login form and block hooks'));
        $bundle->addEvent('form_edit',     'users.ui_hooks.login_screen.form_edit');
        $bundle->addEvent('validate_edit', 'users.ui_hooks.login_screen.validate_edit');
        $bundle->addEvent('process_edit',  'users.ui_hooks.login_screen.process_edit');
        $this->registerHookSubscriberBundle($bundle);

        // Bundle for the login block
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.users.ui_hooks.login_block', 'ui_hooks', $this->__('Login form and block hooks'));
        $bundle->addEvent('form_edit',     'users.ui_hooks.login_block.form_edit');
        $bundle->addEvent('validate_edit', 'users.ui_hooks.login_block.validate_edit');
        $bundle->addEvent('process_edit',  'users.ui_hooks.login_block.process_edit');
        $this->registerHookSubscriberBundle($bundle);
    }
}

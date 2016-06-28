<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Container;

use Zikula\Bundle\HookBundle\AbstractHookContainer;
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;

class HookContainer extends AbstractHookContainer
{
    const EDIT_DISPLAY = 'users.ui_hooks.user.display_view';

    const EDIT_FORM = 'users.ui_hooks.user.form_edit';
    const EDIT_VALIDATE = 'users.ui_hooks.user.validate_edit';
    const EDIT_PROCESS = 'users.ui_hooks.user.process_edit';

    const DELETE_FORM = 'users.ui_hooks.user.form_delete';
    const DELETE_VALIDATE = 'users.ui_hooks.user.validate_delete';
    const DELETE_PROCESS = 'users.ui_hooks.user.process_delete';

    const REGISTRATION_DISPLAY = 'users.ui_hooks.registration.display_view';

    const REGISTRATION_FORM = 'users.ui_hooks.registration.form_edit';
    const REGISTRATION_VALIDATE = 'users.ui_hooks.registration.validate_edit';
    const REGISTRATION_PROCESS = 'users.ui_hooks.registration.process_edit';

    const REGISTRATION_DELETE_FORM = 'users.ui_hooks.registration.form_delete';
    const REGISTRATION_DELETE_VALIDATE = 'users.ui_hooks.registration.validate_delete';
    const REGISTRATION_DELETE_PROCESS = 'users.ui_hooks.registration.process_delete';

    const LOGIN_FORM = 'users.ui_hooks.login_screen.form_edit';
    const LOGIN_VALIDATE = 'users.ui_hooks.login_screen.validate_edit';
    const LOGIN_PROCESS = 'users.ui_hooks.login_screen.process_edit';

    // @todo the LOGIN_BLOCK hooks are not used.
    const LOGIN_BLOCK_FORM = 'users.ui_hooks.login_block.form_edit';
    const LOGIN_BLOCK_VALIDATE = 'users.ui_hooks.login_block.validate_edit';
    const LOGIN_BLOCK_PROCESS = 'users.ui_hooks.login_block.process_edit';

    protected function setupHookBundles()
    {
        // Subscriber bundles
        $bundle = new SubscriberBundle('ZikulaUsersModule', 'subscriber.users.ui_hooks.user', 'ui_hooks', $this->__('User management hooks'));
        $bundle->addEvent('display_view', self::EDIT_DISPLAY);
        $bundle->addEvent('form_edit', self::EDIT_FORM);
        $bundle->addEvent('validate_edit', self::EDIT_VALIDATE);
        $bundle->addEvent('process_edit', self::EDIT_PROCESS);
        $bundle->addEvent('form_delete', self::DELETE_FORM);
        $bundle->addEvent('validate_delete', self::DELETE_VALIDATE);
        $bundle->addEvent('process_delete', self::DELETE_PROCESS);
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new SubscriberBundle('ZikulaUsersModule', 'subscriber.users.ui_hooks.registration', 'ui_hooks', $this->__('Registration management hooks'));
        $bundle->addEvent('display_view', self::REGISTRATION_DISPLAY);
        $bundle->addEvent('form_edit', self::REGISTRATION_FORM);
        $bundle->addEvent('validate_edit', self::REGISTRATION_VALIDATE);
        $bundle->addEvent('process_edit', self::REGISTRATION_PROCESS);
        $bundle->addEvent('form_delete', self::REGISTRATION_DELETE_FORM);
        $bundle->addEvent('validate_delete', self::REGISTRATION_DELETE_VALIDATE);
        $bundle->addEvent('process_delete', self::REGISTRATION_DELETE_PROCESS);
        $this->registerHookSubscriberBundle($bundle);

        // Bundle for the login form
        $bundle = new SubscriberBundle('ZikulaUsersModule', 'subscriber.users.ui_hooks.login_screen', 'ui_hooks', $this->__('Login form and block hooks'));
        $bundle->addEvent('form_edit', self::LOGIN_FORM);
        $bundle->addEvent('validate_edit', self::LOGIN_VALIDATE);
        $bundle->addEvent('process_edit', self::LOGIN_PROCESS);
        $this->registerHookSubscriberBundle($bundle);

        // Bundle for the login block
        // the LOGIN_BLOCK hooks are not used.
        $bundle = new SubscriberBundle('ZikulaUsersModule', 'subscriber.users.ui_hooks.login_block', 'ui_hooks', $this->__('Login form and block hooks'));
        $bundle->addEvent('form_edit', self::LOGIN_BLOCK_FORM);
        $bundle->addEvent('validate_edit', self::LOGIN_BLOCK_VALIDATE);
        $bundle->addEvent('process_edit', self::LOGIN_BLOCK_PROCESS);
        $this->registerHookSubscriberBundle($bundle);
    }
}

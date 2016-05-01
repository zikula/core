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
    const HOOK_VALIDATE_EDIT = 'users.ui_hooks.user.validate_edit';
    const HOOK_VALIDATE_DELETE = 'users.ui_hooks.user.validate_delete';
    const HOOK_USER_EDIT = 'users.ui_hooks.user.form_edit';
    const HOOK_USER_DELETE = 'users.ui_hooks.user.form_delete';
    const HOOK_PROCESS_EDIT = 'users.ui_hooks.user.process_edit';
    const HOOK_PROCESS_DELETE = 'users.ui_hooks.user.process_delete';

    const HOOK_REGISTRATION_VALIDATE = 'users.ui_hooks.registration.validate_edit';
    const HOOK_REGISTRATION_PROCESS = 'users.ui_hooks.registration.process_edit';

    protected function setupHookBundles()
    {
        // Subscriber bundles
        $bundle = new SubscriberBundle('ZikulaUsersModule', 'subscriber.users.ui_hooks.user', 'ui_hooks', $this->__('User management hooks'));
        $bundle->addEvent('display_view', 'users.ui_hooks.user.display_view');
        $bundle->addEvent('form_edit', self::HOOK_USER_EDIT);
        $bundle->addEvent('validate_edit', self::HOOK_VALIDATE_EDIT);
        $bundle->addEvent('process_edit', self::HOOK_PROCESS_EDIT);
        $bundle->addEvent('form_delete', self::HOOK_USER_DELETE);
        $bundle->addEvent('validate_delete', self::HOOK_VALIDATE_DELETE);
        $bundle->addEvent('process_delete', self::HOOK_PROCESS_DELETE);
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new SubscriberBundle('ZikulaUsersModule', 'subscriber.users.ui_hooks.registration', 'ui_hooks', $this->__('Registration management hooks'));
        $bundle->addEvent('display_view', 'users.ui_hooks.registration.display_view');
        $bundle->addEvent('form_edit', 'users.ui_hooks.registration.form_edit');
        $bundle->addEvent('validate_edit', self::HOOK_REGISTRATION_VALIDATE);
        $bundle->addEvent('process_edit', self::HOOK_REGISTRATION_PROCESS);
        $bundle->addEvent('form_delete', 'users.ui_hooks.registration.form_delete');
        $bundle->addEvent('validate_delete', 'users.ui_hooks.registration.validate_delete');
        $bundle->addEvent('process_delete', 'users.ui_hooks.registration.process_delete');
        $this->registerHookSubscriberBundle($bundle);

        // Bundle for the login form
        $bundle = new SubscriberBundle('ZikulaUsersModule', 'subscriber.users.ui_hooks.login_screen', 'ui_hooks', $this->__('Login form and block hooks'));
        $bundle->addEvent('form_edit', 'users.ui_hooks.login_screen.form_edit');
        $bundle->addEvent('validate_edit', 'users.ui_hooks.login_screen.validate_edit');
        $bundle->addEvent('process_edit', 'users.ui_hooks.login_screen.process_edit');
        $this->registerHookSubscriberBundle($bundle);

        // Bundle for the login block
        $bundle = new SubscriberBundle('ZikulaUsersModule', 'subscriber.users.ui_hooks.login_block', 'ui_hooks', $this->__('Login form and block hooks'));
        $bundle->addEvent('form_edit', 'users.ui_hooks.login_block.form_edit');
        $bundle->addEvent('validate_edit', 'users.ui_hooks.login_block.validate_edit');
        $bundle->addEvent('process_edit', 'users.ui_hooks.login_block.process_edit');
        $this->registerHookSubscriberBundle($bundle);
    }
}

<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule;

/**
 * Class UserEvents
 * Contains constant values for user event names, including hook events.
 */
class UserEvents
{
    const REGISTRATION_STARTED = 'module.users.ui.registration.started';
    const REGISTRATION_VALIDATE_NEW = 'module.users.ui.validate_edit.new_registration';
    const REGISTRATION_PROCESS_NEW = 'module.users.ui.process_edit.new_registration';
    const REGISTRATION_SUCCEEDED = 'module.users.ui.registration.succeeded';
    const REGISTRATION_FAILED = 'module.users.ui.registration.failed';

    const HOOK_REGISTRATION_VALIDATE = 'users.ui_hooks.registration.validate_edit';
    const HOOK_REGISTRATION_PROCESS = 'users.ui_hooks.registration.process_edit';
}

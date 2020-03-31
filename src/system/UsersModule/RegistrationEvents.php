<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule;

/**
 * Class RegistrationEvents
 * Contains constant values for user event names, including hook events.
 */
class RegistrationEvents
{
    /**
     * Occurs at the beginning of the registration process, before the registration form is displayed to the user.
     */
    public const REGISTRATION_STARTED = 'module.users.ui.registration.started';

    /**
     * Occurs when an administrator approves a registration. The UserEntity is the subject.
     */
    public const FORCE_REGISTRATION_APPROVAL = 'force.registration.approval';
}

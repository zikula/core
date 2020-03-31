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
     * Occurs after a user attempts to submit a registration request, but the request is not saved successfully.
     * The next step for the user is a page that displays the status, including any possible error messages.
     *   The event subject contains null
     *   The arguments of the event are as follows:
     *     - `'redirectUrl'` will initially contain an empty string. This can be modified to change where the user is
     *       redirected following the failed login.
     *
     * __The `'redirectUrl'` argument__ controls where the user will be directed following a failed log-in attempt.
     * Initially, it will be an empty string, indicating that the user will be redirected to the home page.
     *
     * If a `'redirectUrl'` is specified by any entity intercepting and processing the event, then
     * the user will be redirected to the URL provided, instead of being redirected to the status/error display page.
     * An event handler should carefully consider whether changing the `'redirectUrl'` argument is appropriate.
     * First, the user may be expecting to be directed to a page containing information on why the registration failed.
     * Being redirected to a different page might be disorienting to the user. Second, an event handler that was notified
     * prior to the current handler may already have changed the `'redirectUrl'`.
     */
    public const REGISTRATION_FAILED = 'module.users.ui.registration.failed';

    /**
     * Occurs after a registration record is updated (likely through the admin panel, but not guaranteed).
     * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
     * The subject of the event is set to the UserEntity, with the updated values. The event data contains the
     * original UserEntity in an array `['oldValue' => $originalUser]`.
     */
    public const UPDATE_REGISTRATION = 'user.registration.update';

    /**
     * Occurs when an administrator approves a registration. The UserEntity is the subject.
     */
    public const FORCE_REGISTRATION_APPROVAL = 'force.registration.approval';
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Event;

/**
 * Occurs after a user has successfully registered a new account in the system. It will follow either a
 * `RegistrationPostCreatedEvent`, or a `ActiveUserPostCreatedEvent`, depending on the result of the registration process, the
 * information provided by the user, and several configuration options set in the Users module. The resultant record
 * might be a fully activated user record, or it might be a registration record pending approval, e-mail
 * verification, or both.
 *
 * If the registration record is a fully activated user, and the Users module is configured for automatic log-in,
 * then the system's next step (without any interaction from the user) will be the log-in process. All the customary
 * events that might fire during the log-in process could be fired at this point, including (but not limited to)
 * `Zikula\UsersModule\Event\UserPreLoginSuccessEvent` (which might result in the user having to perform some action
 * in order to proceed with the log-in process), `user.login.succeeded`, and/or `user.login.failed`.
 *
 * The `redirectUrl` property controls where the user will be directed at the end of the registration process.
 * Initially, it will be blank, indicating that the default action should be taken. The default action depends on two
 * things: first, whether the result of the registration process is a registration request record or is a full user record,
 * and second, if the record is a full user record then whether automatic log-in is enabled or not.
 *
 * If a `redirectUrl` is specified by any entity intercepting and processing this event, then
 * how that redirect URL is handled depends on whether the registration process produced a registration request or a full user
 * account record, and if a full user account record was produced then it depends on whether automatic log-in is enabled or
 * not.
 *
 * If the result of the registration process is a registration request record, then by specifying a redirect URL on the event
 * the default action will be overridden, and the user will be redirected to the specified URL at the end of the process.
 *
 * If the result of the registration process is a full user account record and automatic log-in is disabled, then by specifying
 * a redirect URL on the event the default action will be overridden, and the user will be redirected to the specified URL at
 * the end of the process.
 *
 * If the result of the registration process is a full user account record and automatic log-in is enabled, then the user is
 * directed automatically into the log-in process. A redirect URL specified on the event will be passed to the log-in process
 * as the default redirect URL to be used at the end of the log-in process. Note that the user has NOT been automatically
 * redirected to the URL specified on the event. Also note that the log-in process issues its own events, and any one of them
 * could direct the user away from the log-in process and ultimately from the URL specified in this event. Note especially that
 * the log-in process issues its own `UserPostLoginSuccessEvent` that includes the opportunity to set a redirect URL.
 * The URL specified on this event, as mentioned previously, is passed to the log-in process as the default redirect URL, and
 * therefore is offered on the `UserPostLoginSuccessEvent` event as the default. Any handler of that event, however, has
 * the opportunity to change the redirect URL offered. A handler can reliably predict
 * whether the user will be directed into the log-in process automatically by inspecting the Users module variable
 * `Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN` (which evaluates to `'reg_autologin'`), and by inspecting the `'activated'`
 * status of the registration or user object received.
 *
 * An event handler should carefully consider whether changing the `'redirectUrl'` argument is appropriate. First, the user may
 * be expecting to return to the log-in screen . Being redirected to a different page might be disorienting to the user. Second,
 * an event handler that was notified prior to the current handler may already have changed the `'redirectUrl'`.
 */
class RegistrationPostSuccessEvent extends RedirectableUserEntityEvent
{
}

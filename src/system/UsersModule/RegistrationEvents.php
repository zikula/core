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
 * Class RegistrationEvents
 * Contains constant values for user event names, including hook events.
 */
class RegistrationEvents
{
    /**
     * Occurs at the beginning of the registration process, before the registration form is displayed to the user.
     */
    const REGISTRATION_STARTED = 'module.users.ui.registration.started';

    const REGISTRATION_VALIDATE_NEW = 'module.users.ui.validate_edit.new_registration';

    const REGISTRATION_VALIDATE_MODIFY = 'module.users.ui.validate_edit.modify_registration';

    const REGISTRATION_PROCESS_NEW = 'module.users.ui.process_edit.new_registration';

    const REGISTRATION_PROCESS_MODIFY = 'module.users.ui.process_edit.modify_registration';

    /**
     * Occurs after a user has successfully registered a new account in the system. It will follow either a
     * `registration.create` event, or a `user.create` event, depending on the result of the registration process, the
     * information provided by the user, and several configuration options set in the Users module. The resultant record
     * might be a fully activated user record, or it might be a registration record pending approval, e-mail
     * verification, or both.
     *
     * If the registration record is a fully activated user, and the Users module is configured for automatic log-in,
     * then the system's next step (without any interaction from the user) will be the log-in process. All the customary
     * events that might fire during the log-in process could be fired at this point, including (but not limited to)
     * `user.login.veto` (which might result in the user having to perform some action in order to proceed with the
     * log-in process), `user.login.succeeded`, and/or `user.login.failed`.
     *
     * The event's subject is set to the registration record (which might be a full user record).
     * The event's arguments are as follows:
     * `'returnurl'` A URL to which the user is redirected at the very end of the registration process.
     *
     * __The `'redirecturl'` argument__ controls where the user will be directed at the end of the registration process.
     * Initially, it will be blank, indicating that the default action should be taken. The default action depends on two
     * things: first, whether the result of the registration process is a registration request record or is a full user record,
     * and second, if the record is a full user record then whether automatic log-in is enabled or not.
     *
     * If the result of the registration process is a registration request record, then the default action is to direct the
     * user to a status display screen that informs him that the registration process has been completed, and also tells
     * him what next steps are required in order to convert that request into a full user record. (The steps to be
     * taken may be out of the user's control--for example, the administrator must approve the request. The steps to
     * be taken might be within the user's control--for example, the user must verify his e-mail address. The steps might
     * be some combination of both within and outside the user's control.
     *
     * If the result of the registration process is a full user record, then one of two actions will happen by default. Either
     * the user will be directed to the log-in screen, or the user will be automatically logged in. Which of these two occurs
     * is dependent on a module variable setting in the Users module. During the login process, one or more additional events may
     * fire.
     *
     * If a `'redirecturl'` is specified by any entity intercepting and processing the `user.registration.succeeded` event, then
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
     * the log-in process issues its own `module.users.ui.login.succeeded` event that includes the opportunity to set a redirect URL.
     * The URL specified on this event, as mentioned previously, is passed to the log-in process as the default redirect URL, and
     * therefore is offered on the `module.users.ui.login.succeeded` event as the default. Any handler of that event, however, has
     * the opportunity to change the redirect URL offered. A `module.users.ui.registration.succeeded` handler can reliably predict
     * whether the user will be directed into the log-in process automatically by inspecting the Users module variable
     * `Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN` (which evaluates to `'reg_autologin'`), and by inspecting the `'activated'`
     * status of the registration or user object received.
     *
     * An event handler should carefully consider whether changing the `'redirecturl'` argument is appropriate. First, the user may
     * be expecting to return to the log-in screen . Being redirected to a different page might be disorienting to the user. Second,
     * all event handlers are being notified of this event. This is not a `notify()` event. An event handler that was notified
     * prior to the current handler may already have changed the `'redirecturl'`.
     */
    const REGISTRATION_SUCCEEDED = 'module.users.ui.registration.succeeded';

    /**
     * Occurs after a user attempts to submit a registration request, but the request is not saved successfully.
     * The next step for the user is a page that displays the status, including any possible error messages.
     *   The event subject contains null
     *   The arguments of the event are as follows:
     *     - `'redirecturl'` will initially contain an empty string. This can be modified to change where the user is
     *       redirected following the failed login.
     *
     * __The `'redirecturl'` argument__ controls where the user will be directed following a failed log-in attempt.
     * Initially, it will be an empty string, indicating that the user will be redirected to a page that displays status
     *   and error information.
     *
     * If a `'redirecturl'` is specified by any entity intercepting and processing the event, then
     * the user will be redirected to the URL provided, instead of being redirected to the status/error display page.
     * An event handler should carefully consider whether changing the `'redirecturl'` argument is appropriate.
     * First, the user may be expecting to be directed to a page containing information on why the registration failed.
     * Being redirected to a different page might be disorienting to the user. Second, all event handlers are being
     * notified of this event. This is not a `notify()` event. An event handler that was notified prior to the current
     * handler may already have changed the `'redirecturl'`.
     */
    const REGISTRATION_FAILED = 'module.users.ui.registration.failed';

    const HOOK_REGISTRATION_VALIDATE = 'users.ui_hooks.registration.validate_edit';
    const HOOK_REGISTRATION_PROCESS = 'users.ui_hooks.registration.process_edit';

    /**
     * Occurs after a registration record is created, either through the normal user registration process, or through
     * the administration panel for the Users module. This event will not fire if the result of the registration process
     * is a full user record. Instead, a user.account.create event will fire.
     * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
     * The subject of the event is set to the registration record that was created.
     */
    const CREATE_REGISTRATION = 'user.registration.create';

    /**
     * Occurs after a registration record is updated (likely through the admin panel, but not guaranteed).
     * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
     * The subject of the event is set to the registration record, with the updated values.
     */
    const UPDATE_REGISTRATION = 'user.registration.update';

    /**
     * Occurs after a registration record is deleted. This could occur as a result of the administrator deleting the
     * record through the approval/denial process, or it could happen because the registration request expired. This
     * event will not fire if a registration record is converted to a full user account record. Instead, a
     * `user.account.create` event will fire. This is a storage-level event, not a UI event. It should not be used for
     * UI-level actions such as redirects.
     * The subject of the event is set to the registration record begin deleted.
     */
    const DELETE_REGISTRATION = 'user.registration.delete';

    /**
     * A hook-like event triggered when the registration form is displayed, which allows other modules to intercept
     * and display their own elements for submission on the registration form.
     * To add elements to the registration form, render output and add this as an array element on the event's
     * data array.
     * There is no subject and no arguments for the event.
     */
    const FORM_REGISTRATION_NEW = 'module.users.ui.form_edit.new_registration';

    /**
     * A hook-like event triggered when the administrator's modify registration form is displayed, which allows other
     * modules to intercept and display their own elements for submission on the new user form.
     * To add elements to the modify registration form, render output and add this as an array element on the event's
     * data array.
     * The subject contains the current state of the registration object, possibly edited from its original state.
     * The `'id'` argument contains the uid of the registration record.
     */
    const FORM_REGISTRATION_MODIFY = 'module.users.ui.form_edit.modify_registration';
}

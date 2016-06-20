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
 * Class AccessEvents
 */
class AccessEvents
{
    /**
     * Occurs at the beginning of the log-in process, before the registration form is displayed to the user.
     * NOTE: This event will not fire if the log-in process is entered through any other method other than visiting the
     * log-in screen directly. For example, if automatic log-in is enabled following registration, then this event
     * will not fire when the system passes control from the registration process to the log-in process.
     * Likewise, this event will not fire if a user begins the log-in process from the log-in block or a log-in
     * plugin if the user provides valid authentication information. This event will fire, however, if invalid
     * information is provided to the log-in block or log-in plugin, resulting in the user being
     * redirected to the full log-in screen for corrections.
     * This event does not have any subject, arguments, or data.
     */
    const LOGIN_STARTED = 'module.users.ui.login.started';

    /**
     * Occurs immediately prior to a log-in that is expected to succeed. (All prerequisites for a
     * successful login have been checked and are satisfied.) This event allows a module to
     * intercept the login process and prevent a successful login from taking place.
     *
     * A handler that needs to veto a login attempt
     * should call `stopPropagation()`. This will prevent other handlers from receiving the event, will
     * return to the login process, and will prevent the login from taking place. A handler that
     * vetoes a login attempt should set an appropriate session flash message and give any additional
     * feedback to the user attempting to log in that might be appropriate.
     *
     * If vetoing the login, the 'returnUrl' argument should be set to redirect the user to an appropriate action.
     *
     * Note: the user __will not__ be logged in at the point where the event handler is
     * executing. Any attempt to check a user's permissions, his logged-in status, or any
     * operation will return a value equivalent to what an anonymous (guest) user would see. Care
     * should be taken to ensure that sensitive operations done within a handler for this event
     * do not introduce breaches of security.
     *
     * The subject of the event will contain the userEntity
     * The arguments of the event are:
     * `'authenticationMethod'` will contain the alias (name) of the method that was used to authenticate the user.
     */
    const LOGIN_VETO = 'user.login.veto';

    /**
     * Occurs right after a successful attempt to log in, and just prior to redirecting the user to the desired page.
     *
     * The event subject contains the userEntity
     * The arguments of the event are as follows:
     * `'authenticationMethod'` will contain the alias (name) of the method that was used to authenticate the user.
     * `'returnUrl'` will contain the value of the 'returnUrl' parameter, if one was supplied, or an empty
     * string. This can be modified to change where the user is redirected following the login.
     *
     * If a `'returnUrl'` is specified by any entity intercepting and processing the `user.login.succeeded` event, then
     * the URL provided replaces the one provided by the returnUrl parameter to the login process. If it is set to an empty
     * string, then the user is redirected to the site's home page. An event handler should carefully consider whether
     * changing the `'returnUrl'` argument is appropriate. First, the user may be expecting to return to the page where
     * he was when he initiated the log-in process. Being redirected to a different page might be disorienting to the user.
     * Second, an event handler that was notified prior to the current handler may already have changed the `'returnUrl'`.
     *
     * Finally, this event only fires in the event of a "normal" UI-oriented log-in attempt. A module attempting to log in
     * programmatically by directly calling the login function will not see this event fired.
     */
    const LOGIN_SUCCESS = 'module.users.ui.login.succeeded';

    /**
     * Occurs right after an unsuccessful attempt to log in.
     *
     * The event subject contains the userEntity if it has been found, otherwise null.
     * The arguments of the event are as follows:
     * `'authenticationMethod'` will contain the alias (name) of the method that was used to authenticate the user.
     * `'methodId'` is the id provided by the ReEntrant method (if provided).
     * `'returnUrl'` This can be modified to change where the user is redirected following the failed login.
     *
     * If a `'returnUrl'` is specified by any entity intercepting and processing the `user.login.failed` event, then
     * the user will be redirected to the URL provided.  An event handler
     * should carefully consider whether changing the `'returnUrl'` argument is appropriate. First, the user may be expecting
     * to return to the log-in screen . Being redirected to a different page might be disorienting to the user.
     * Second, an event handler that was notified prior to the current handler may already have changed the `'returnUrl'`.
     *
     * Finally, this event only fires in the event of a "normal" UI-oriented log-in attempt. A module attempting to log in
     * programmatically by directly calling core functions will not see this event fired.
     */
    const LOGIN_FAILED = 'module.users.ui.login.failed';

    /**
     * Occurs right after a successful logout.
     * The event's subject contains the user's UserEntity
     * Args contain array of `['authentication_method' => $authenticationMethod,
     * 'uid'=> $uid];`
     */
    const LOGOUT_SUCCESS = 'module.users.ui.logout.succeeded';

    /**
     * A hook-like UI event that is triggered when the login screen is displayed. This allows another module to
     * intercept the display of the full-page version of the login form to add its own form elements for submission.
     * To add elements to the form, render the output and add this as an array element to the event's
     * data array.
     * This event does not have any subject, arguments, or data.
     */
    const LOGIN_FORM = 'module.users.ui.form_edit.login_screen';
    const LOGIN_VALIDATE = 'module.users.ui.validate_edit.login_screen';
    const LOGIN_PROCESS = 'module.users.ui.process_edit.login_screen';
}

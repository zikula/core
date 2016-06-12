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
     * Occurs immediately prior to a log-in that is expected to succeed. (All prerequisites for a
     * successful login have been checked and are satisfied.) This event allows a module to
     * intercept the login process and prevent a successful login from taking place.
     *
     * This event uses `notify()`, so handlers are called until either one vetoes the login attempt,
     * or there are no more handlers for the event. A handler that needs to veto a login attempt
     * should call `stopPropagation()`. This will prevent other handlers from receiving the event, will
     * return to the login process, and will prevent the login from taking place. A handler that
     * vetoes a login attempt should set an appropriate error message and give any additional
     * feedback to the user attempting to log in that might be appropriate. If a handler does not
     * need to veto the login attempt, then it should simply return null (`return;` with no
     * return value).
     *
     * Note: the user __will not__ be logged in at the point where the event handler is
     * executing. Any attempt to check a user's permissions, his logged-in status, or any
     * operation will return a value equivalent to what an anonymous (guest) user would see. Care
     * should be taken to ensure that sensitive operations done within a handler for this event
     * do not introduce breaches of security.
     *
     * The subject of the event will contain the user's account record, equivalent to
     * `UserUtil::getVars($uid)`.
     * The arguments of the event are:
     * `'authentication_method'` will contain the name of the module and the name of the method that was used to authenticated the user.
     * `'uid'` will contain the user's uid.
     *
     * An event handler can prevent (veto) the log-in attempt by calling `stopPropagation()` on the event. This is
     * enough to ensure that the log-in attempt is stopped, however this will result in a `Zikula_Exception_Forbidden`
     * exception being thrown.
     *
     * To, instead, redirect the user back to the log-in screen (after possibly setting an error message that will
     * be displayed), then set the event data to contain an array with a single element, `retry`, having a value
     * of true (e.g., `$event->setData(['retry' => true]);`).  This will signal the log-in process to go back
     * to the log-in screen for another attempt. The expectation is that the notifying event handler has set an
     * error message, and that the user will be able to log-in if the instructions in that message are followed,
     * or the conditions in that message can be met.
     *
     * The Legal module uses this method when vetoing an attempt, if the Legal module has established a hook with the
     * log-in screen. The user is redirected back to the log-in screen and now that the user is known, the
     * Legal module is able to display a form fragment directly on the log-in screen which allows the user
     * to accept the policies that remain unaccepted. Assuming that the user accepts the policies, his
     * next attempt at logging in will be successful because the condition in the Legal module that caused the
     * veto no longer exists.
     *
     * Another alternative is to "break into" the log-in process to redirect the user to a form (or something
     * similar) that allows him to correct whatever situation is causing his log-in attempt to be vetoed. The
     * expectation is that the notifying event handler will direct the user to a form to correct the situation,
     * and then __redirect the user back into the log-in process to re-attempt logging in__. To accomplish this,
     * instead of setting the `'retry'` event data, the notifying handler should set the `'redirect_func'`
     * event data structure. This is an array which defines the information necessary to direct the
     * user to a controller function somewhere in the Zikula system (likely, within the same module as that
     * which is vetoing the attempt). This array contains the following:
     *
     * `'modname'` The name of the module where the controller function is defined.
     * `'type'` The library type that defines the function.
     * `'func'` The name of the function itself.
     * `'args'` An array of function argument key-value pairs to pass to the function when calling it. Since the function
     * will be called through a redirect, any parameters will be converted to GET parameters on the URL, so
     * the developer should consider the minimum set to include--preferably none. Session variables are an
     * alternative to passing function arguments.
     *
     * In addition, if information from the log-in attempt is needed within the function, it can be made available in
     * session variables. To do this, add an array called `'session'` to the `'redirect_func'` array structure. The contents
     * of the `'session'` array must be:
     *
     * `'namespace'` The session name space in which to store the variable.
     * `'var'` The name of the session variable.
     *
     * An array will be stored in that variable, containing information from the log-in process. The elements of this array will
     * be:
     *
     * `'returnurl'` The URL where the user should be redirected upon successfully logging in.
     * `'authentication_info'` An array containing the authentication information entered by the user. The contents
     * of this array depends entirely on the authentication method.
     * `'authentication_method'` An array containing the `'modname'` (module name) of the authentication module, and
     * the `'method'` name of the authentication method being used by the user who is logging in.
     * `'rememberme'` A flag indicating whether the user checked the box to remain logged in.
     * `'user_obj'` The user object array (same as received when calling `UserUtil::getVars($uid);`) of the user who is
     * logging in.
     *
     * This information is also passed back to the log-in process when the user is redirected back there.
     * 
     * The Users module uses this method to handle users who have been forced by the administrator to change their password
     * prior to logging in. The code used for the notification might look like the following example:
     *
     * $event->stopPropagation();
     * $event->setData([
     * 'redirect_func'  => [
     * 'modname'   => 'ZikulaUsersModule',
     * 'type'      => 'user',
     * 'func'      => 'changePassword',
     * 'args'      => [
     * 'login'     => true
     * ],
     * 'session'   => [
     * 'var'       => 'Users_Controller_User_changePassword',
     * 'namespace' => 'Zikula_Users'
     * ]
     * ]
     * ]);
     *
     * LogUtil::registerError(__("Your log-in request was not completed. You must change your web site account's password first."));
     *
     * In this example, the user will be redirected to the URL pointing to the `changePassword` function. This URL is constructed by calling
     * `ModUtil::url()` with the modname, type, func, and args specified in the above array. The `changePassword` function also needs access
     * to the information from the log-in attempt, which will be stored in the session variable and namespace specified. This is accomplished
     * by calling `SessionUtil::setVar()` prior to the redirect, as follows:
     *
     * SessionUtil::setVar('Users_Controller_User_changePassword', $sessionVars, 'Zikula_Users' true, true);
     *
     * where `$sessionVars` contains the information discussed previously.
     */
    const LOGIN_VETO = 'user.login.veto';

    /**
     * Occurs right after a successful attempt to log in, and just prior to redirecting the user to the desired page.
     * All handlers are notified.
     *
     * The event subject contains the user's user record (from `UserUtil::getVars($event['uid'])`)
     * The arguments of the event are as follows:
     * `'authentication_module'` an array containing the authenticating module name (`'modname'`) and method (`'method'`)
     * used to log the user in.
     * `'redirecturl'` will contain the value of the 'returnurl' parameter, if one was supplied, or an empty
     * string. This can be modified to change where the user is redirected following the login.
     *
     * __The `'redirecturl'` argument__ controls where the user will be directed at the end of the log-in process.
     * Initially, it will be the value of the returnurl parameter provided to the log-in process, or blank if none was provided.
     *
     * The action following login depends on whether WCAG compliant log-in is enabled in the Users module or not. If it is enabled,
     * then the user is redirected to the returnurl immediately. If not, then the user is first displayed a log-in landing page,
     * and then meta refresh is used to redirect the user to the returnurl.
     *
     * If a `'redirecturl'` is specified by any entity intercepting and processing the `user.login.succeeded` event, then
     * the URL provided replaces the one provided by the returnurl parameter to the login process. If it is set to an empty
     * string, then the user is redirected to the site's home page. An event handler should carefully consider whether
     * changing the `'redirecturl'` argument is appropriate. First, the user may be expecting to return to the page where
     * he was when he initiated the log-in process. Being redirected to a different page might be disorienting to the user.
     * Second, all event handlers are being notified of this event. This is not a `notify()` event. An event handler
     * that was notified prior to the current handler may already have changed the `'redirecturl'`.
     *
     * Finally, this event only fires in the event of a "normal" UI-oriented log-in attempt. A module attempting to log in
     * programmatically by directly calling the core functions will not see this event fired.
     */
    const LOGIN_SUCCESS = 'module.users.ui.login.succeeded';

    /**
     * Occurs right after an unsuccessful attempt to log in. All handlers are notified.
     *
     * The event subject contains the user's user record (from `UserUtil::getVars($event['uid'])`) if it has been found, otherwise null
     * The arguments of the event are as follows:
     * `'authentication_module'` an array containing the authenticating module name (`'modname'`) and method (`'method'`)
     * used to log the user in.
     * `'authentication_info'` an array containing the authentication information entered by the user (contents will vary by method).
     * `'redirecturl'` will initially contain an empty string. This can be modified to change where the user is redirected following the failed login.
     *
     * __The `'redirecturl'` argument__ controls where the user will be directed following a failed log-in attempt.
     * Initially, it will be an empty string, indicating that the user should continue with the log-in process and be presented
     * with the log-in form.
     *
     * If a `'redirecturl'` is specified by any entity intercepting and processing the `user.login.failed` event, then
     * the user will be redirected to the URL provided, instead of being presented with the log-in form.  An event handler
     * should carefully consider whether changing the `'redirecturl'` argument is appropriate. First, the user may be expecting
     * to return to the log-in screen . Being redirected to a different page might be disorienting to the user.
     * Second, all event handlers are being notified of this event. This is not a `notify()` event. An event handler
     * that was notified prior to the current handler may already have changed the `'redirecturl'`.
     *
     * Finally, this event only fires in the event of a "normal" UI-oriented log-in attempt. A module attempting to log in
     * programmatically by directly calling core functions will not see this event fired.
     */
    const LOGIN_FAILED = 'module.users.ui.login.failed';
}

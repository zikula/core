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
 * Class AccessEvents
 */
class AccessEvents
{
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
    public const LOGIN_SUCCESS = 'module.users.ui.login.succeeded';

    /**
     * Occurs right after an unsuccessful attempt to log in.
     *
     * The event subject contains the userEntity if it has been found, otherwise null.
     * The arguments of the event are as follows:
     * `'authenticationMethod'` will contain an instance of the authenticationMethod used that produced the failed login.
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
    public const LOGIN_FAILED = 'module.users.ui.login.failed';

    /**
     * Occurs right after a successful logout.
     * The event's subject contains the user's UserEntity
     * Args contain array of `['authentication_method' => $authenticationMethod,
     * 'uid'=> $uid];`
     */
    public const LOGOUT_SUCCESS = 'module.users.ui.logout.succeeded';
}

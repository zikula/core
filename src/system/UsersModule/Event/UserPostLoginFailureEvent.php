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

namespace Zikula\UsersModule\Event;

use Zikula\UsersModule\Entity\UserEntity;

/**
 * Occurs right after an unsuccessful attempt to log in.
 *
 * The event contains the userEntity if it has been found, otherwise null.
 *
 * If a `'returnUrl'` is specified by any entity intercepting and processing this event, then
 * the user will be redirected to the URL provided.  An event handler
 * should carefully consider whether changing the `'returnUrl'` argument is appropriate. First, the user may be expecting
 * to return to the log-in screen . Being redirected to a different page might be disorienting to the user.
 * Second, an event handler that was notified prior to the current handler may already have changed the `'returnUrl'`.
 *
 * Finally, this event only fires in the event of a "normal" UI-oriented log-in attempt. A module attempting to log in
 * programmatically by directly calling core functions will not see this event fired.
 */
class UserPostLoginFailureEvent extends RedirectableUserEntityEvent implements AuthMethodAwareInterface
{
    use AuthMethodTrait;

    public function __construct(?UserEntity $userEntity, string $authenticationMethod)
    {
        parent::__construct($userEntity);
        $this->authenticationMethod = $authenticationMethod;
    }
}

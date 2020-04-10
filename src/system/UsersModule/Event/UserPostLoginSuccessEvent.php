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

use Zikula\UsersModule\Entity\UserEntity;

/**
 * Occurs right after a successful attempt to log in, and just prior to redirecting the user to the desired page.
 *
 * If a `'returnUrl'` is specified by any entity intercepting and processing the event, then
 * the URL provided replaces the one provided by the returnUrl parameter to the login process. If it is set to an empty
 * string, then the user is redirected to the site's home page. An event handler should carefully consider whether
 * changing the `'returnUrl'` argument is appropriate. First, the user may be expecting to return to the page where
 * he was when he initiated the log-in process. Being redirected to a different page might be disorienting to the user.
 * Second, an event handler that was notified prior to the current handler may already have changed the `'returnUrl'`.
 *
 * Finally, this event only fires in the event of a "normal" UI-oriented log-in attempt. A module attempting to log in
 * programmatically by directly calling the login function will not see this event fired.
 */
class UserPostLoginSuccessEvent extends RedirectableUserEntityEvent implements AuthMethodAwareInterface
{
    use AuthMethodTrait;

    public function __construct(UserEntity $userEntity, string $authenticationMethod)
    {
        parent::__construct($userEntity);
        $this->authenticationMethod = $authenticationMethod;
    }

    public function isFirstLogin(): bool
    {
        $defaultLastLogin = new \DateTime('1970-01-01 00:00:00');
        $actualLastLogin = $this->getUser()->getLastlogin();
        if (null === $actualLastLogin || $actualLastLogin === $defaultLastLogin) {
            return true;
        }

        return false;
    }
}

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
 * Event called on user login handle form submission.
 * See also LoginFormPostCreatedEvent
 */
class LoginFormPostValidatedEvent extends UserFormPostValidatedEvent
{
}

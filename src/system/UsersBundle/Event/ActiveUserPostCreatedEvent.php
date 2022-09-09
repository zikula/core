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

namespace Zikula\UsersBundle\Event;

/**
 * Occurs after a user account is created. All handlers are notified. It does not apply to creation of a pending
 * registration. This is a storage-level event,
 * not a UI event. It should not be used for UI-level actions such as redirects.
 */
class ActiveUserPostCreatedEvent extends UserEntityEvent
{
}
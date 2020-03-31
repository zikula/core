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

/**
 * Occurs after a user is updated. All handlers are notified.
 * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
 * The User property is the *new* data. The oldUser property is the *old* data
 */
class ActiveUserPostUpdatedEvent extends UserEntityChangedEvent
{
}

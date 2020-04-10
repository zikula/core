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
 * Occurs after a registration record is updated (likely through the admin panel, but not guaranteed).
 * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
 * The User property is the *new* data. The oldUser property is the *old* data
 */
class RegistrationPostUpdatedEvent extends UserEntityChangedEvent
{
}

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
 * Occurs after a registration record is deleted. This could occur as a result of the administrator deleting the
 * record through the approval/denial process, or it could happen because the registration request expired. This
 * event will not fire if a registration record is converted to a full user account record. Instead, a
 * `user.account.create` event will fire. This is a storage-level event, not a UI event. It should not be used for
 * UI-level actions such as redirects.
 * The subject of the event is set to the Uid being deleted.
 */
class DeletedRegistrationEvent extends UserEntityEvent
{
}

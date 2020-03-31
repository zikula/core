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
 * Occurs when the Registration process is determining whether to create a 'registration' or a 'full user'.
 *
 * If the User hasn't been persisted, then there will be no Uid.
 *
 * A handler that needs to veto a registration should call `stopPropagation()`. This will prevent other handlers
 * from receiving the event, will return to the registration process, and will prevent the registration from
 * creating a 'full user' record.
 *
 * For example an authentication method may veto a registration attempt if it requires a user to verify some
 * registration data by email.
 *
 * It is assumed that the authentication method will have notified the user of required steps to prevent future
 * vetoes. And provide the methods to correct the issue and process the steps.
 *
 * Because this event will not necessarily notify ALL listeners (if propagation is stopped) it CANNOT be relied upon
 * to effect change of any kind with regard to the entity.
 */
class CreateActiveUserEvent extends StoppableUserEntityEvent
{
}

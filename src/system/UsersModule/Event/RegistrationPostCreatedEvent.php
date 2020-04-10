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
 * Occurs after a registration record is created, either through the normal user registration process, or through
 * the administration panel for the Users module. The creation of a registration record does not imply
 * that registration was fully successful. Use the RegistrationPostSuccessEvent::class for that purpose.
 * This event will not fire if the result of the registration process
 * is a full user record. Instead, a ActiveUserPostCreatedEvent::class will fire.
 * This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.
 * This event occurs before the $authenticationMethod->register() method is called.
 */
class RegistrationPostCreatedEvent extends UserEntityEvent
{
}

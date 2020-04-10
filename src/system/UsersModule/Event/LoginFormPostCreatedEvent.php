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

use Zikula\Bundle\FormExtensionBundle\Event\FormPostCreatedEvent;

/**
 * Event called on user login, allowing additions to the login form.
 * This event *is* also fired on the login block. Additions to the form in this
 *   case will force the user to login via the standard login page.
 * See also LoginFormPostValidatedEvent
 */
class LoginFormPostCreatedEvent extends FormPostCreatedEvent
{
}

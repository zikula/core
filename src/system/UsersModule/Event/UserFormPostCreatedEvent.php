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

use Zikula\Bundle\FormExtensionBundle\Event\FormPostCreatedEvent;

/**
 * Called on user registration or admin user creation.
 * See also UserFormPostValidatedEvent
 */
class UserFormPostCreatedEvent extends FormPostCreatedEvent
{
}

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

namespace Zikula\Bundle\HookBundle\Validator;

use Zikula\Bundle\HookBundle\Hook\Connection;
use Zikula\Bundle\HookBundle\Hook\HookEventListenerInterface;

class ConnectionValidator
{
    /**
     * Can listener listen to the assigned event?
     */
    public function isValidListener(Connection $connection): bool
    {
        // Does listener implement ListenerInterface?
        $implementsInterface = \is_subclass_of($connection->getListener(), HookEventListenerInterface::class);
        // Is event a subclass of the listened to event?
        $eventIsSubClassed = \is_subclass_of($connection->getEvent(), $connection->getListener()->listensTo());

        return $implementsInterface && $eventIsSubClassed;
    }
}

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

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * An UserEntityEvent that adds the ability to stop the propagation.
 */
class StoppableUserEntityEvent extends UserEntityEvent implements StoppableEventInterface
{
    use StoppableTrait;
}

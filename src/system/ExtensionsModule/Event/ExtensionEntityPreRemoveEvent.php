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

namespace Zikula\ExtensionsModule\Event;

/**
 * Occurs before an extension is removed.
 * Stop propagation of the event to prevent extension removal.
 */
class ExtensionEntityPreRemoveEvent extends StoppableExtensionEntityEvent
{
}

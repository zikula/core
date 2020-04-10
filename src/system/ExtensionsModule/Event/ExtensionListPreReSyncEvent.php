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

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Occurs when extension list is viewed and can veto the re-syncing of the extension list.
 * Stop propagation of the event to prevent re-sync.
 */
class ExtensionListPreReSyncEvent extends Event
{
}

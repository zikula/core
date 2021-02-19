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

namespace Zikula\Bundle\HookBundle\HookEventListener;

use Zikula\Bundle\HookBundle\HookEvent\DisplayHookEvent;

/**
 * A DisplayHookEventListener responds to DisplayHookEvents and can add
 * DisplayHookEventReponses to the Event for later display.
 */
abstract class DisplayHookEventListener implements HookEventListenerInterface
{
    final public function listensTo(): string
    {
        return DisplayHookEvent::class;
    }

    final public function getClassname(): string
    {
        return static::class;
    }
}

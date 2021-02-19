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

use Zikula\Bundle\HookBundle\HookEvent\FilterHookEvent;

/**
 * A FilterHookEventListener responds to FilterHookEvents and can modify/filter
 * the provided data.
 */
abstract class FilterHookEventListener implements HookEventListenerInterface
{
    final public function listensTo(): string
    {
        return FilterHookEvent::class;
    }

    final public function getClassname(): string
    {
        return static::class;
    }
}

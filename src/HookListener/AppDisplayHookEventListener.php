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

namespace App\HookListener;

use Zikula\Bundle\HookBundle\HookEvent\DisplayHookEvent;
use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\DisplayHookEventListener;
use Zikula\Bundle\HookBundle\HookEventResponse\DisplayHookEventResponse;

final class AppDisplayHookEventListener extends DisplayHookEventListener
{
    public function getTitle(): string
    {
        return 'App DisplayHook Listener';
    }

    public function getInfo(): string
    {
        return 'App DisplayHook Listener Info - long text. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }

    public function execute(HookEvent $event): void
    {
        // Strongly encouraged to check for exact HookEvent type here because typehint required to be generic
        if ($event instanceof DisplayHookEvent) {
            $event->addResponse(new DisplayHookEventResponse(self::class, '🤣 bar-' . $event->getId()));
        }
    }
}

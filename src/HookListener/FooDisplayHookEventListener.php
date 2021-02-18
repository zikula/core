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

final class FooDisplayHookEventListener extends DisplayHookEventListener
{
    public function getTitle(): string
    {
        return 'Foo DisplayHook Listener';
    }

    public function getInfo(): string
    {
        return 'Foo DisplayHook Listener Info - long text. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }

    public function execute(HookEvent $event): void
    {
        // Strongly encouraged to check for exact HookEvent type here because typehint required to be generic
        if ($event instanceof DisplayHookEvent) {
            $event->addResponse(new DisplayHookEventResponse(self::class, 'This is the Foo Hook Response'));
        }
    }
}

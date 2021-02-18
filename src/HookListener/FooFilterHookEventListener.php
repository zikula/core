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

use Zikula\Bundle\HookBundle\HookEvent\FilterHookEvent;
use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\FilterHookEventListener;

final class FooFilterHookEventListener extends FilterHookEventListener
{
    public function getTitle(): string
    {
        return 'FUBAR!';
    }

    public function getInfo(): string
    {
        return 'Change a few words.';
    }

    public function execute(HookEvent $event): void
    {
        // Strongly encouraged to check for exact HookEvent type here because typehint required to be generic
        if ($event instanceof FilterHookEvent) {
            $event->setData(str_replace(['star', 'are', 'fat', 'cat'], ['<b>bike</b>', '<b>mike</b>', '<b>low</b>', '<b>bow</b>'], $event->getData()));
        }
    }
}

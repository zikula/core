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

namespace App\Hook;

use Zikula\Bundle\HookBundle\HookEvent\FilterHookEvent;
use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\FilterHookEventListener;
use function Symfony\Component\String\u;

final class AppFilterHookEventListener extends FilterHookEventListener
{
    public function getTitle(): string
    {
        return 'Filter Twinkle!';
    }

    public function getInfo(): string
    {
        return 'App Info - long text. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }

    public function execute(HookEvent $event): void
    {
        // Strongly encouraged to check for exact HookEvent type here because typehint required to be generic
        if ($event instanceof FilterHookEvent) {
            $event->setData(str_replace(['star', 'are', 'high', 'sky'], ['<b>biz</b>', '<b>is</b>', '<b>fat</b>', '<b>cat</b>'], $event->getData()));
        }
    }
}


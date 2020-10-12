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

namespace Zikula\UsersModule\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zikula\UsersModule\Twig\Runtime\MessageRuntime;

class MessageExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('messageSendLink', [MessageRuntime::class, 'messageSendLink'], ['is_safe' => ['html']])
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('messageInboxLink', [MessageRuntime::class, 'messageInboxLink'], ['is_safe' => ['html']]),
            new TwigFunction('messageCount', [MessageRuntime::class, 'messageCount'])
        ];
    }
}

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

namespace Zikula\Bundle\HookBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zikula\Bundle\HookBundle\Twig\Runtime\HookRuntime;

class HookExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('notifyDisplayHooks', [HookRuntime::class, 'notifyDisplayHooks'], ['is_safe' => ['html']]),
            new TwigFunction('routeUrl', [HookRuntime::class, 'createRouteUrl'])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('notifyFilters', [HookRuntime::class, 'notifyFilters'], ['is_safe' => ['html']])
        ];
    }
}

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

class HookExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('notifyDisplayHooks', [$this, 'notifyDisplayHooks'], ['is_safe' => ['html']]),
            new TwigFunction('routeUrl', [$this, 'createRouteUrl'])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('notifyFilters', [$this, 'notifyFilters'], ['is_safe' => ['html']])
        ];
    }
}

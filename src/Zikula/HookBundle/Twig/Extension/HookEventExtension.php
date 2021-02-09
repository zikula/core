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
use Zikula\Bundle\HookBundle\Twig\Runtime\HookEventRuntime;

class HookEventExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('dispatchFilterHookEvent', [HookEventRuntime::class, 'dispatchFilterHookEvent'], ['is_safe' => ['html']])
        ];
    }
}

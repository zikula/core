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

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\Bundle\CoreBundle\Twig\Runtime\EventDispatcherRuntime;

class EventDispatcherExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('dispatchEvent', [EventDispatcherRuntime::class, 'dispatchEvent']),
            new TwigFunction('dispatchGenericEvent', [EventDispatcherRuntime::class, 'dispatchGenericEvent'])
        ];
    }
}

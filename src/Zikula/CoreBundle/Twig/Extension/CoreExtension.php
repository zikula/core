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
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zikula\Bundle\CoreBundle\Twig\Runtime\CoreRuntime;
use Zikula\Bundle\CoreBundle\Twig\TokenParser\SwitchTokenParser;

class CoreExtension extends AbstractExtension
{
    public function getTokenParsers()
    {
        return [
            new SwitchTokenParser()
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('array_unset', [CoreRuntime::class, 'arrayUnset']),
            new TwigFunction('callFunc', [CoreRuntime::class, 'callFunc'])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('yesNo', [CoreRuntime::class, 'yesNo']),
            new TwigFilter('php', [CoreRuntime::class, 'applyPhp']),
            new TwigFilter('protectMail', [CoreRuntime::class, 'protectMailAddress'], ['is_safe' => ['html']])
        ];
    }
}

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

namespace Zikula\ExtensionsModule\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\ExtensionsModule\Twig\Runtime\ModVarRuntime;

class ModVarExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getModVar', [ModVarRuntime::class, 'getModVar']),
            new TwigFunction('getSystemVar', [ModVarRuntime::class, 'getSystemVar'])
        ];
    }
}

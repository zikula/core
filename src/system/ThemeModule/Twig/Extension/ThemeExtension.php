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

namespace Zikula\ThemeModule\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\ThemeModule\Twig\Runtime\ThemeRuntime;

class ThemeExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('pageAddAsset', [ThemeRuntime::class, 'pageAddAsset']),
            new TwigFunction('getPreviewImagePath', [ThemeRuntime::class, 'getPreviewImagePath'], ['is_safe' => ['html']]),
            new TwigFunction('zasset', [ThemeRuntime::class, 'getAssetPath'])
        ];
    }
}

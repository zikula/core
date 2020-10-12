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

namespace Zikula\AdminModule\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\AdminModule\Twig\Runtime\AdminRuntime;

class AdminExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('adminHeader', [AdminRuntime::class, 'adminHeader'], ['is_safe' => ['html']]),
            new TwigFunction('adminBreadcrumbs', [AdminRuntime::class, 'adminBreadcrumbs'], ['is_safe' => ['html']]),
            new TwigFunction('adminUpdateCheck', [AdminRuntime::class, 'adminUpdateCheck'], ['is_safe' => ['html']]),
            new TwigFunction('adminSecurityAnalyzer', [AdminRuntime::class, 'adminSecurityAnalyzer'], ['is_safe' => ['html']]),
            new TwigFunction('adminMenu', [AdminRuntime::class, 'adminMenu'], ['is_safe' => ['html']]),
            new TwigFunction('adminPanelMenu', [AdminRuntime::class, 'adminPanelMenu'], ['is_safe' => ['html']]),
            new TwigFunction('adminFooter', [AdminRuntime::class, 'adminFooter'], ['is_safe' => ['html']])
        ];
    }
}

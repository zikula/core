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

namespace Zikula\DefaultThemeBundle\Twig;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Extension\RuntimeExtensionInterface;

class DefaultThemeBundleRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public function getStyleChoices(): array
    {
        $themeBundle = $this->kernel->getBundle('ZikulaDefaultThemeBundle');
        $themeVarsPath = $themeBundle->getConfigPath() . '/variables.yaml';
        $variableDefinitions = Yaml::parse(file_get_contents($themeVarsPath));

        return $variableDefinitions['theme_style']['options']['choices'];
    }
}

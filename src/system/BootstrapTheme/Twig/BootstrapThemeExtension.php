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

namespace Zikula\BootstrapTheme\Twig;

use Symfony\Component\Yaml\Yaml;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class BootstrapThemeExtension extends AbstractExtension
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getStyleChoices', [$this, 'getStyleChoices'])
        ];
    }

    public function getStyleChoices(): array
    {
        $themeBundle = $this->kernel->getBundle('ZikulaBootstrapTheme');
        $themeVarsPath = $themeBundle->getConfigPath() . '/variables.yaml';
        $variableDefinitions = Yaml::parse(file_get_contents($themeVarsPath));

        return $variableDefinitions['theme_style']['options']['choices'];
    }
}

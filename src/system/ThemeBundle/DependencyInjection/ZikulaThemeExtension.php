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

namespace Zikula\ThemeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\ThemeBundle\Engine\Engine;
use Zikula\ThemeBundle\EventListener\DefaultPageAssetSetterListener;
use Zikula\ThemeBundle\EventListener\OutputCompressionListener;
use Zikula\ThemeBundle\EventListener\ResponseTransformerListener;

class ZikulaThemeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(Engine::class)
            ->setArgument('$defaultDashboard', $config['default_dashboard'])
            ->setArgument('$adminDashboard', $config['admin_dashboard']);

        $container->getDefinition(OutputCompressionListener::class)
            ->setArgument('$useCompression', $config['use_compression']);
    }
}

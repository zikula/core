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

namespace Zikula\SecurityCenterModule\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\SecurityCenterModule\Listener\ClickjackProtectionListener;

class ZikulaSecurityCenterExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        if (!isset($container->getExtensions()['framework'])) {
            return;
        }
        $configs = $container->getExtensionConfig($this->getAlias());
        $zikulaSCConfig = $this->processConfiguration(new Configuration(), $configs);
        $container->prependExtensionConfig('framework', ['session' => $zikulaSCConfig['session']]);
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(ClickjackProtectionListener::class)
            ->setArgument('$xFrameOptions', $config['x_frame_options']);

        $container->setParameter('zikula.session.name', $config['session']['name']);
    }
}

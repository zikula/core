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

namespace Zikula\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CoreExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        if (!isset($container->getExtensions()['maker'])) {
            return;
        }
        $configs = $container->getExtensionConfig($this->getAlias());
        $zikulaCoreConfig = $this->processConfiguration(new Configuration(), $configs);
        if (!empty($zikulaCoreConfig['maker_root_namespace'])) {
            $container->prependExtensionConfig('maker', ['root_namespace' => $zikulaCoreConfig['maker_root_namespace']]);
        }
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('session.yaml');
        $loader->load('services.yaml');
        $loader->load('translation.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('datadir', $config['datadir']);
        $container->setParameter('multisites', $config['multisites']);
    }

    public function getNamespace(): string
    {
        return 'http://symfony.com/schema/dic/symfony';
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\Core\LinkContainer\LinkContainerInterface;

/**
 * CoreExtension class.
 */
class CoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('symfony_overrides.yml');
        $loader->load('session.yml');
        $loader->load('services.yml');

        $container->registerForAutoconfiguration(LinkContainerInterface::class)
            ->addTag('zikula.link_container')
        ;
    }

    public function getNamespace(): string
    {
        return 'http://symfony.com/schema/dic/symfony';
    }
}

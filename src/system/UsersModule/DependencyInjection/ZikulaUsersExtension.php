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

namespace Zikula\UsersModule\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\UsersModule\AuthenticationMethodInterface\AuthenticationMethodInterface;
use Zikula\UsersModule\MessageModule\MessageModuleInterface;
use Zikula\UsersModule\ProfileModule\ProfileModuleInterface;

class ZikulaUsersExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yml');

        $container->registerForAutoconfiguration(AuthenticationMethodInterface::class)
            ->addTag('zikula.authentication_method')
        ;
        $container->registerForAutoconfiguration(MessageModuleInterface::class)
            ->addTag('zikula.message_module')
        ;
        $container->registerForAutoconfiguration(ProfileModuleInterface::class)
            ->addTag('zikula.profile_module')
        ;
    }
}

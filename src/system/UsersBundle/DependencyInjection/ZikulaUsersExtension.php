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

namespace Zikula\UsersBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\UsersBundle\Helper\MailHelper;
use Zikula\UsersBundle\Menu\ExtensionMenu;

class ZikulaUsersExtension extends Extension implements PrependExtensionInterface
{
    private ?bool $isNucleosSelfDeletionEnabled = null;

    public function prepend(ContainerBuilder $containerBuilder)
    {
        // get all bundles
        $bundles = $containerBuilder->getParameter('kernel.bundles');
        if (isset($bundles['NucleosUserBundle'])) {
            $config = $containerBuilder->getExtensionConfig('nucleos_user')[0];
            $this->isNucleosSelfDeletionEnabled = $config['deletion']['enabled'] ?? null;
        }
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(MailHelper::class)
            ->setArgument('$registrationNotificationEmail', $config['registration']['admin_notification_mail']);

        $container->getDefinition(ExtensionMenu::class)
            ->setArgument('$registrationEnabled', $this->isNucleosSelfDeletionEnabled ?? $config['registration']['enabled'])
            ->setArgument('$allowSelfDeletion', $config['allow_self_deletion']);
    }
}

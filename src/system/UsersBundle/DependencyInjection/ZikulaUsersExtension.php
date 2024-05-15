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
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Zikula\UsersBundle\Form\Type\AvatarType;
use Zikula\UsersBundle\Helper\MailHelper;
use Zikula\UsersBundle\Helper\ProfileHelper;
use Zikula\UsersBundle\Helper\UploadHelper;
use Zikula\UsersBundle\Menu\ExtensionMenu;
use Zikula\UsersBundle\Twig\Runtime\ProfileRuntime;

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

        $container->getDefinition(ExtensionMenu::class)
            ->setArgument('$registrationEnabled', $config['registration']['enabled'])
            ->setArgument('$allowSelfDeletion', $this->isNucleosSelfDeletionEnabled ?? $config['allow_self_deletion']);

        $container->getDefinition(AvatarType::class)
            ->setArgument('$avatarConfig', $config['avatar']);

        $container->getDefinition(MailHelper::class)
            ->setArgument('$registrationNotificationEmail', $config['registration']['admin_notification_mail']);

        $container->getDefinition(ProfileHelper::class)
            ->setArgument('$avatarImagePath', $config['avatar']['image_path'])
            ->setArgument('$avatarDefaultImage', $config['avatar']['default_image'])
            ->setArgument('$gravatarEnabled', $config['avatar']['gravatar_enabled']);

        $container->getDefinition(UploadHelper::class)
            ->setArgument('$uploadConfig', $config['avatar']['uploads'])
            ->setArgument('$imagePath', $config['avatar']['image_path']);

        $container->getDefinition(ProfileRuntime::class)
            ->setArgument('$displayRegistrationDate', $config['display_registration_date']);
    }
}

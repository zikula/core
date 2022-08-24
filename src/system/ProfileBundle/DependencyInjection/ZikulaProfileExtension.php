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

namespace Zikula\ProfileBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\ProfileBundle\Bridge\ProfileBundleBridge;
use Zikula\ProfileBundle\Controller\ProfileController;
use Zikula\ProfileBundle\EventListener\UsersUiListener;
use Zikula\ProfileBundle\Form\Type\AvatarType;
use Zikula\ProfileBundle\Helper\UploadHelper;

class ZikulaProfileExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(ProfileBundleBridge::class)
            ->setArgument('$avatarImagePath', $config['avatar']['image_path'])
            ->setArgument('$avatarDefaultImage', $config['avatar']['default_image'])
            ->setArgument('$gravatarEnabled', $config['avatar']['gravatar_enabled']);

        $container->getDefinition(ProfileController::class)
            ->setArgument('$displayRegistrationDate', $config['display_registration_date'])
            ->setArgument('$avatarImagePath', $config['avatar']['image_path']);

        $container->getDefinition(UsersUiListener::class)
            ->setArgument('$displayRegistrationDate', $config['display_registration_date'])
            ->setArgument('$avatarImagePath', $config['avatar']['image_path']);

        $container->getDefinition(AvatarType::class)
            ->setArgument('$avatarConfig', $config['avatar']);

        $container->getDefinition(UploadHelper::class)
            ->setArgument('$uploadConfig', $config['avatar']['uploads'])
            ->setArgument('$imagePath', $config['avatar']['image_path']);
    }
}

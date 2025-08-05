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

namespace Zikula\UsersBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zikula\CoreBundle\Bundle\Initializer\BundleInitializerInterface;
use Zikula\CoreBundle\Bundle\Initializer\InitializableBundleInterface;
use Zikula\CoreBundle\Bundle\MetaData\BundleMetaDataInterface;
use Zikula\CoreBundle\Bundle\MetaData\MetaDataAwareBundleInterface;
use Zikula\UsersBundle\Bundle\Initializer\UsersInitializer;
use Zikula\UsersBundle\Bundle\MetaData\UsersBundleMetaData;
use Zikula\UsersBundle\Form\Type\AvatarType;
use Zikula\UsersBundle\Helper\MailHelper;
use Zikula\UsersBundle\Helper\ProfileHelper;
use Zikula\UsersBundle\Helper\UploadHelper;
use Zikula\UsersBundle\Menu\ExtensionMenu;
use Zikula\UsersBundle\Twig\TwigExtension;

class ZikulaUsersBundle extends AbstractBundle implements InitializableBundleInterface, MetaDataAwareBundleInterface
{
    private ?bool $isNucleosSelfDeletionEnabled = null;

    public function getMetaData(): BundleMetaDataInterface
    {
        return $this->container->get(UsersBundleMetaData::class);
    }

    public function getInitializer(): BundleInitializerInterface
    {
        return $this->container->get(UsersInitializer::class);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // get all bundles
        $bundles = $builder->getParameter('kernel.bundles');
        if (isset($bundles['NucleosUserBundle'])) {
            $config = $builder->getExtensionConfig('nucleos_user')[0];
            $this->isNucleosSelfDeletionEnabled = $config['deletion']['enabled'] ?? null;
        }
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        // configure services
        $services = $container->services();

        $services->get(ExtensionMenu::class)
            ->arg('$registrationEnabled', $config['registration']['enabled'])
            ->arg('$allowSelfDeletion', $this->isNucleosSelfDeletionEnabled ?? $config['allow_self_deletion']);

        $services->get(AvatarType::class)
            ->arg('$avatarConfig', $config['avatar']);

        $services->get(MailHelper::class)
            ->arg('$registrationNotificationEmail', $config['registration']['admin_notification_mail']);

        $services->get(ProfileHelper::class)
            ->arg('$avatarImagePath', $config['avatar']['image_path'])
            ->arg('$avatarDefaultImage', $config['avatar']['default_image'])
            ->arg('$gravatarEnabled', $config['avatar']['gravatar_enabled']);

        $services->get(UploadHelper::class)
            ->arg('$uploadConfig', $config['avatar']['uploads'])
            ->arg('$imagePath', $config['avatar']['image_path']);

        $services->get(TwigExtension::class)
            ->arg('$displayRegistrationDate', $config['display_registration_date']);
    }
}

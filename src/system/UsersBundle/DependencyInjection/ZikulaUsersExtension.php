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
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\UsersBundle\Controller\AccountController;
use Zikula\UsersBundle\Controller\RegistrationController;
use Zikula\UsersBundle\Controller\UserAdministrationController;
use Zikula\UsersBundle\Helper\MailHelper;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\UsersBundle\Menu\ExtensionMenu;
use Zikula\UsersBundle\ProfileBundle\ProfileBundleCollector;
use Zikula\UsersBundle\Validator\Constraints\ValidEmailValidator;
use Zikula\UsersBundle\Validator\Constraints\ValidUnameValidator;

class ZikulaUsersExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(AccountController::class)
            ->setArgument('$displayGraphics', $config['display_graphics_on_account_page'])
            ->setArgument('$allowSelfDeletion', $config['allow_self_deletion']);

        $container->getDefinition(RegistrationController::class)
            ->setArgument('$registrationRequiresApproval', $config['registration']['moderation'])
            ->setArgument('$registrationDisabledReason', $config['registration']['disabled_reason'])
            ->setArgument('$useAutoLogin', $config['registration']['auto_login'])
            ->setArgument('$illegalUserAgents', $config['registration']['illegal_user_agents']);

        $container->getDefinition(UserAdministrationController::class)
            ->setArgument('$itemsPerPage', $config['items_per_page']);

        $container->getDefinition(ProfileBundleCollector::class)
            ->setArgument('$currentProfileBundleName', $config['integration']['profile_bundle']);

        $container->getDefinition(MailHelper::class)
            ->setArgument('$registrationNotificationEmail', $config['registration']['admin_notification_mail']);
        $container->getDefinition(RegistrationHelper::class)
            ->setArgument('$registrationEnabled', $config['registration']['enabled'])
            ->setArgument('$registrationRequiresApproval', $config['registration']['moderation'])
            ->setArgument('$registrationNotificationEmail', $config['registration']['admin_notification_mail']);

        $container->getDefinition(ExtensionMenu::class)
            ->setArgument('$allowSelfDeletion', $config['allow_self_deletion']);

        $container->getDefinition(ValidEmailValidator::class)
            ->setArgument('$illegalDomains', $config['registration']['illegal_domains']);
        $container->getDefinition(ValidUnameValidator::class)
            ->setArgument('$illegalUserNames', $config['registration']['illegal_user_names']);
    }
}

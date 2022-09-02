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
use Zikula\ThemeBundle\EventListener\OutputCompressionListener;
use Zikula\ThemeBundle\Controller\Dashboard\AdminDashboardController;
use Zikula\ThemeBundle\Controller\Dashboard\UserDashboardController;

class ZikulaThemeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('zikula_theme_user_dashboard', $config['user_dashboard']['class']);
        $container->setParameter('zikula_theme_admin_dashboard', $config['admin_dashboard']['class']);

        $container->getDefinition(AdminDashboardController::class)
            ->setArgument('$themeConfig', $config['admin_dashboard']);

        $container->getDefinition(UserDashboardController::class)
            ->setArgument('$themeConfig', $config['user_dashboard']);

        $container->getDefinition(OutputCompressionListener::class)
            ->setArgument('$useCompression', $config['use_compression']);
    }
}

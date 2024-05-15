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
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Zikula\ThemeBundle\Controller\Dashboard\AdminDashboardController;
use Zikula\ThemeBundle\Controller\Dashboard\UserDashboardController;
use Zikula\ThemeBundle\EventSubscriber\OutputCompressionSubscriber;

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
            ->setArgument('$themeConfig', $this->prepareThemeConfig($config['admin_dashboard']));

        $container->getDefinition(UserDashboardController::class)
            ->setArgument('$themeConfig', $this->prepareThemeConfig($config['user_dashboard']));

        $container->getDefinition(OutputCompressionSubscriber::class)
            ->setArgument('$useCompression', $config['use_compression']);
    }

    private function prepareThemeConfig(array $themeConfig): array
    {
        if (null === $themeConfig['content']['redirect']['route']) {
            return $themeConfig;
        }
        if (empty($themeConfig['content']['redirect']['route_parameters'])) {
            return $themeConfig;
        }

        foreach ($themeConfig['content']['redirect']['route_parameters'] as $key => $entry) {
            $themeConfig['content']['redirect']['route_parameters'][$key] = $entry['value'];
        }

        return $themeConfig;
    }
}

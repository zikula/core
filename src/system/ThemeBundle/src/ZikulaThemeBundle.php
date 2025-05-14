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

namespace Zikula\ThemeBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zikula\CoreBundle\Bundle\MetaData\BundleMetaDataInterface;
use Zikula\CoreBundle\Bundle\MetaData\MetaDataAwareBundleInterface;
use Zikula\ThemeBundle\Bundle\MetaData\ThemeBundleMetaData;
use Zikula\ThemeBundle\Controller\Dashboard\AdminDashboardController;
use Zikula\ThemeBundle\Controller\Dashboard\UserDashboardController;
use Zikula\ThemeBundle\EventSubscriber\OutputCompressionSubscriber;

class ZikulaThemeBundle extends AbstractBundle implements MetaDataAwareBundleInterface
{
    public function getMetaData(): BundleMetaDataInterface
    {
        return $this->container->get(ThemeBundleMetaData::class);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        // set parameters
        $container->parameters()
            ->set('zikula_theme_user_dashboard', $config['user_dashboard']['class'])
            ->set('zikula_theme_admin_dashboard', $config['admin_dashboard']['class']);

        // configure services
        $services = $container->services();

        $services->get(AdminDashboardController::class)
            ->arg('$themeConfig', $this->prepareThemeConfig($config['admin_dashboard']));

        $services->get(UserDashboardController::class)
            ->arg('$themeConfig', $this->prepareThemeConfig($config['user_dashboard']));

        $services->get(OutputCompressionSubscriber::class)
            ->arg('$useCompression', $config['use_compression']);
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

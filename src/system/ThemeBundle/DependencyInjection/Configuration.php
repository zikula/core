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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Zikula\ThemeBundle\Controller\Dashboard\AdminDashboardController;
use Zikula\ThemeBundle\Controller\Dashboard\UserDashboardController;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zikula_theme');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('default_dashboard')
                    ->info('Dashboard controller to use for main site.')
                    ->defaultValue(UserDashboardController::class)
                ->end()
                ->scalarNode('admin_dashboard')
                    ->info('Dashboard controller to use for the admin area. Leave empty to use site\'s dashboard.')
                    ->defaultValue(AdminDashboardController::class)
                ->end()
                ->booleanNode('use_compression')
                    ->info('Whether to enable output compression (requires PHP Zlib extension).')
                    ->defaultFalse()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

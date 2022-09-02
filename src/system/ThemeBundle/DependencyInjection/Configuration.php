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
                ->arrayNode('user_dashboard')
                    ->info('Dashboard for main site.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->info('FQCN of the dashboard controller used for the user dashboard.')
                            ->defaultValue(UserDashboardController::class)
                        ->end()
                        ->arrayNode('view')
                            ->info('Default view option.')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('content_maximized')
                                    ->info('Whether the page content should span the entire browser width, instead of a defined max width.')
                                    ->defaultTrue()
                                ->end()
                                ->booleanNode('sidebar_minimized')
                                    ->info('Whether the sidebar (which contains the main menu) should be displayed as a narrow column instead of the expanded design.')
                                    ->defaultFalse()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('content')
                            ->info('Index page behavior.')
                            ->addDefaultsIfNotSet()
                            ->validate()
                                ->ifTrue(static function ($v) {
                                    $amount = 0;
                                    $amount += isset($v['template']) ? 1 : 0;
                                    $amount += isset($v['redirect']['crud']) ? 1 : 0;
                                    $amount += isset($v['redirect']['route']) ? 1 : 0;

                                    return 1 < $amount;
                                })
                                ->thenInvalid('User dashboard index page content must only be one of template, crud redirect or route redirect.')
                            ->end()
                            ->children()
                                ->scalarNode('template')
                                    ->info('Render a template at the specified path.')
                                    ->defaultValue('@ZikulaTheme/welcome.html.twig')
                                ->end()
                                ->arrayNode('redirect')
                                    ->info('Redirect to a specific page.')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('crud')
                                            ->info('FQCN of a CRUD controller to use as default.')
                                            ->defaultNull()
                                        ->end()
                                        ->scalarNode('route')
                                            ->info('Arbitrary route to use as default.')
                                            ->defaultNull()
                                        ->end()
                                        ->arrayNode('route_parameters')
                                            ->info('Array of route parameters (each defined by "name" and "value" entries).')
                                            ->useAttributeAsKey('name')
                                            ->arrayPrototype()
                                                ->children()
                                                    ->scalarNode('name')->end()
                                                    ->scalarNode('value')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('admin_dashboard')
                    ->info('Dashboard for admin area.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->info('FQCN of the dashboard controller used for the admin dashboard.')
                            ->defaultValue(AdminDashboardController::class)
                        ->end()
                        ->arrayNode('view')
                            ->info('Default view option.')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('content_maximized')
                                    ->info('Whether the page content should span the entire browser width, instead of a defined max width.')
                                    ->defaultTrue()
                                ->end()
                                ->booleanNode('sidebar_minimized')
                                    ->info('Whether the sidebar (which contains the main menu) should be displayed as a narrow column instead of the expanded design.')
                                    ->defaultFalse()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('content')
                            ->info('Index page behavior.')
                            ->addDefaultsIfNotSet()
                            ->validate()
                                ->ifTrue(static function ($v) {
                                    $amount = 0;
                                    $amount += isset($v['template']) ? 1 : 0;
                                    $amount += isset($v['redirect']['crud']) ? 1 : 0;
                                    $amount += isset($v['redirect']['route']) ? 1 : 0;

                                    return 1 < $amount;
                                })
                                ->thenInvalid('Admin dashboard index page content must only be one of template, crud redirect or route redirect.')
                            ->end()
                            ->children()
                                ->scalarNode('template')
                                    ->info('Render a template at the specified path.')
                                    ->defaultNull()
                                ->end()
                                ->arrayNode('redirect')
                                    ->info('Redirect to a specific page.')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('crud')
                                            ->info('FQCN of a CRUD controller to use as default.')
                                            ->defaultNull()
                                        ->end()
                                        ->scalarNode('route')
                                            ->info('Arbitrary route to use as default.')
                                            ->defaultValue('zikulathemebundle_branding_overview')
                                        ->end()
                                        ->arrayNode('route_parameters')
                                            ->info('Array of route parameters (each defined by "name" and "value" entries).')
                                            ->useAttributeAsKey('name')
                                            ->arrayPrototype()
                                                ->children()
                                                    ->scalarNode('name')->end()
                                                    ->scalarNode('value')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
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

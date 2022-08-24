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

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('zikula_theme');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('default_theme')
                    ->info('Theme bundle to use for main site.')
                    ->defaultValue('ZikulaDefaultThemeBundle')
                ->end()
                ->scalarNode('admin_theme')
                    ->info('Theme bundle to use for admin controllers. Leave empty to use site\'s theme.')
                    ->defaultNull()
                ->end()
                ->enumNode('script_position')
                    ->info('Where to insert additional script tags referencing JavaScript sources.')
                    ->values(['head', 'foot'])
                    ->defaultValue('foot')
                ->end()
                ->booleanNode('trimwhitespace')
                    ->info('Whether to trim any whitespace from the response or not.')
                    ->defaultFalse()
                ->end()
                ->arrayNode('bootstrap')
                    ->info('Paths to Bootstrap installation.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('css_path')
                            ->info('Path to Bootstrap CSS file.')
                            ->defaultValue('/bootstrap/css/bootstrap.min.css')
                        ->end()
                        ->scalarNode('js_path')
                            ->info('Path to Bootstrap JS file.')
                            ->defaultValue('/bootstrap/js/bootstrap.bundle.min.js')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('font_awesome_path')
                    ->info('Path to FontAwesome CSS file.')
                    ->defaultValue('/font-awesome/css/all.min.css')
                ->end()
                ->booleanNode('use_compression')
                    ->info('Whether to enable output compression (requires PHP Zlib extension).')
                    ->defaultFalse()
                ->end()
                ->arrayNode('asset_manager')
                    ->info('Asset handling options.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('combine')
                            ->info('Whether to combine assets or not.')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('lifetime')
                            ->info('Lifetime until cached assets are renewed.')
                            ->defaultValue('1 day')
                        ->end()
                        ->booleanNode('compress')
                            ->info('Whether output compression should be applied on combined assets or not (requires PHP Zlib extension).')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('minify')
                            ->info('Remove comments, whitespace and spaces from css files.')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

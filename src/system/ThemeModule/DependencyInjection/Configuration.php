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

namespace Zikula\ThemeModule\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('zikula_theme');

        $treeBuilder->getRootNode()
            ->children()
                ->enumNode('script_position')
                    ->values(['head', 'foot'])
                    ->defaultValue('foot')
                ->end()
                ->booleanNode('trimwhitespace')->defaultFalse()->end()
                ->arrayNode('bootstrap')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('css_path')->defaultValue('/bootstrap/css/bootstrap.min.css')->end()
                        ->scalarNode('js_path')->defaultValue('/bootstrap/js/bootstrap.bundle.min.js')->end()
                    ->end()
                ->end() // bootstrap
                ->scalarNode('font_awesome_path')->defaultValue('/font-awesome/css/all.min.css')->end()
                ->arrayNode('asset_manager')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('combine')->defaultFalse()->end()
                        ->scalarNode('lifetime')->defaultValue('1 day')->end()
                        ->booleanNode('compress')->defaultTrue()->end()
                        ->booleanNode('minify')->defaultTrue()->end()
                    ->end()
                ->end() // zikula_asset_manager
            ->end()
        ;

        return $treeBuilder;
    }
}

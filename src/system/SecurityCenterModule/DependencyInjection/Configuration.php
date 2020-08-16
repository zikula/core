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

namespace Zikula\SecurityCenterModule\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('zikula_security_center');

        $treeBuilder->getRootNode()
            ->children()
                ->enumNode('x_frame_options')
                    ->values(['SAMEORIGIN', 'DENY'])
                    ->defaultValue('SAMEORIGIN')
                ->end()
                ->arrayNode('session')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue('_zsid')->end()
                        ->scalarNode('handler_id')->defaultValue('session.handler.native_file')->end()
                        ->scalarNode('storage_id')->defaultValue('zikula_core.bridge.http_foundation.zikula_session_storage_file')->end()
                        ->scalarNode('save_path')->defaultValue('%kernel.cache_dir%/sessions')->end()
                        ->enumNode('cookie_secure')
                            ->values([true, false, 'auto'])
                            ->defaultValue('auto')
                        ->end()
                    ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

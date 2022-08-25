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

namespace Zikula\GroupsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zikula_groups');

        $treeBuilder->getRootNode()
            ->children()
                ->integerNode('groups_per_page')
                    ->info('Number of groups displayed per page.')
                    ->defaultValue(25)
                    ->min(1)
                ->end()
                ->integerNode('default_group')
                    ->info('ID of initial user group.')
                    ->defaultValue(1)
                    ->min(1)
                ->end()
                ->booleanNode('hide_closed_groups')
                    ->info('Whether closed groups should be hidden on the overview page or not.')
                    ->defaultFalse()
                ->end()
                ->booleanNode('hide_private_groups')
                    ->info('Whether private groups should be hidden on the overview page or not.')
                    ->defaultFalse()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

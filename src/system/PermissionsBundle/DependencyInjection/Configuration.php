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

namespace Zikula\PermissionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zikula_permissions');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('lock_admin_rule')
                    ->info('Lock main administration permission rule.')
                    ->defaultTrue()
                ->end()
                ->integerNode('admin_rule_id')
                    ->info('ID of main administration permission rule.')
                    ->defaultValue(1)
                    ->min(1)
                ->end()
                ->booleanNode('enable_filtering')
                    ->info('Enable filtering of group permissions.')
                    ->defaultTrue()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

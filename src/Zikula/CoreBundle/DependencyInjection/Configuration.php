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

namespace Zikula\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const DEFAULT_DATADIR = 'public/uploads';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('core');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('datadir')->defaultValue(self::DEFAULT_DATADIR)->end()
                ->scalarNode('maker_root_namespace')->defaultNull()->end()
                ->arrayNode('multisites')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('mainsiteurl')->defaultNull()->end()
                        ->scalarNode('based_on_domains')->defaultNull()->end()
                        ->arrayNode('protected_systemvars')->addDefaultsIfNotSet()->end()
                    ->end()
                ->end() // multisites
            ->end()
        ;

        return $treeBuilder;
    }
}

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

namespace Zikula\RoutesModule\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Zikula\RoutesModule\Translation\ZikulaPatternGenerationStrategy;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('zikula_routes');

        $treeBuilder->getRootNode()
            ->children()
                ->enumNode('jms_i18n_routing_strategy')
                    ->values([ZikulaPatternGenerationStrategy::STRATEGY_PREFIX, ZikulaPatternGenerationStrategy::STRATEGY_PREFIX_EXCEPT_DEFAULT])
                    ->defaultValue(ZikulaPatternGenerationStrategy::STRATEGY_PREFIX_EXCEPT_DEFAULT)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

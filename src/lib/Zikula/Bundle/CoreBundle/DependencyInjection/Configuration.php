<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * CoreExtension configuration structure.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var bool
     */
    private $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('framework');
        $rootNode = $treeBuilder->getRootNode();
        $this->addTranslatorSection($rootNode);

        return $treeBuilder;
    }

    protected function addTranslatorSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('translator')
            ->info('translator configuration')
            ->canBeEnabled()
            ->fixXmlConfig('fallback')
            ->children()
            ->arrayNode('fallbacks')
            ->beforeNormalization()->ifString()->then(static function($v) {
                return [$v];
            })->end()
            ->prototype('scalar')->end()
            ->defaultValue(['en'])
            ->end()
            ->booleanNode('logging')->defaultValue($this->debug)->end()
            ->end()
            ->end()
            ->end()
        ;
    }
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
    private $debug;

    /**
     * Constructor
     *
     * @param boolean $debug Whether to use the debug mode
     */
    public function __construct($debug)
    {
        $this->debug = (bool)$debug;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('framework');
        $this->addTranslatorSection($rootNode);

        return $treeBuilder;
    }

    protected function addTranslatorSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
        ->children()
        ->arrayNode('translator')
        ->info('translator configuration')
        ->canBeEnabled()
        ->fixXmlConfig('fallback')
        ->children()
        ->arrayNode('fallbacks')
        ->beforeNormalization()->ifString()->then(function($v) {
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

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * FrameworkExtension configuration structure.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    private $debug;

    /**
     * Constructor
     *
     * @param Boolean $debug Whether to use the debug mode
     */
    public function  __construct($debug)
    {
        $this->debug = (Boolean) $debug;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
//        $rootNode = $treeBuilder->root('framework');
//
//        $rootNode
//            ->children()
//                ->scalarNode('charset')->setInfo('general configuration')->end()
//                ->scalarNode('trust_proxy_headers')->defaultFalse()->end()
//                ->scalarNode('secret')->isRequired()->end()
//                ->scalarNode('ide')->defaultNull()->end()
//                ->booleanNode('test')->end()
//                ->scalarNode('default_locale')->defaultValue('en')->end()
//            ->end()
//        ;
//
//        $this->addFormSection($rootNode);
//        $this->addEsiSection($rootNode);
//        $this->addProfilerSection($rootNode);
//        $this->addRouterSection($rootNode);
//        $this->addSessionSection($rootNode);
//        $this->addTemplatingSection($rootNode);
//        $this->addTranslatorSection($rootNode);
//        $this->addValidationSection($rootNode);
//        $this->addAnnotationsSection($rootNode);

        return $treeBuilder;
    }
}

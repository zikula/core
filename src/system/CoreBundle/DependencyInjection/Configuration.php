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

namespace Zikula\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('core');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('datadir')
                    ->info('Directory for data files which might be added or uploaded.')
                    ->defaultValue('public/uploads')
                ->end()
                ->enumNode('x_frame_options')
                    ->info('X-Frame-Options value.')
                    ->values(['SAMEORIGIN', 'DENY'])
                    ->defaultValue('SAMEORIGIN')
                ->end()
                ->arrayNode('maintenance_mode')
                    ->info('Disable site for maintenance.')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('reason')
                            ->info('Reason for disabling site.')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('enable_mail_logging')
                    ->info('Whether mail logging should be used or not.')
                    ->defaultFalse()
                ->end()
                ->booleanNode('multilingual')
                    ->info('Activate multilingual features.')
                    ->defaultTrue()
                ->end()
                ->arrayNode('site_data')
                    ->info('Main site information (if desired per locale).')
                    ->useAttributeAsKey('locale')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('locale')
                                ->info('Locale code.')
                                ->defaultValue('en')
                            ->end()
                            ->scalarNode('sitename')
                                ->info('Site name.')
                                ->defaultValue('My site')
                            ->end()
                            ->scalarNode('slogan')
                                ->info('Site description.')
                                ->defaultValue('This is my site.')
                            ->end()
                            ->scalarNode('page_title_scheme')
                                ->info('Page title scheme. Possible tags: #pagetitle#, #sitename#.')
                                ->defaultValue('#pagetitle# - #sitename#')
                            ->end()
                            ->scalarNode('meta_description')
                                ->info('Meta description.')
                                ->defaultValue('This is my site description.')
                            ->end()
                            ->scalarNode('admin_mail')
                                ->info('Admin mail address.')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

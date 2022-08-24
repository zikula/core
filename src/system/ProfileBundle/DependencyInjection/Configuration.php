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

namespace Zikula\ProfileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('zikula_profile');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('display_registration_date')
                    ->info('Display the user\'s registration date.')
                    ->defaultFalse()
                ->end()
                ->arrayNode('avatar')
                    ->info('Avatar settings.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('image_path')
                            ->info('Path to user\'s avatar images.')
                            ->defaultValue('public/uploads/avatar')
                        ->end()
                        ->scalarNode('default_image')
                            ->info('Default avatar image (used as fallback).')
                            ->defaultValue('gravatar.jpg')
                        ->end()
                        ->booleanNode('gravatar_enabled')
                            ->info('Allow usage of Gravatar.')
                            ->defaultTrue()
                        ->end()
                        ->arrayNode('uploads')
                            ->info('Allow uploading custom avatar images.')
                            ->canBeEnabled()
                            ->children()
                                ->booleanNode('shrink_large_images')
                                    ->info('Shrink large images to maximum dimensions.')
                                    ->defaultTrue()
                                ->end()
                                ->integerNode('max_size')
                                    ->info('Max. avatar filesize in bytes.')
                                    ->defaultValue(12000)
                                    ->min(1)
                                ->end()
                                ->integerNode('max_width')
                                    ->info('Max. avatar width in pixels.')
                                    ->defaultValue(80)
                                    ->min(1)
                                    ->max(9999)
                                ->end()
                                ->integerNode('max_height')
                                    ->info('Max. avatar height in pixels.')
                                    ->defaultValue(80)
                                    ->min(1)
                                    ->max(9999)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

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

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void
{
    $definition->rootNode()
        ->fixXmlConfig('policy')
        ->children()
            ->arrayNode('policies')
                ->info('Configure the available policies.')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('legal_notice')
                        ->info('Legal notice.')
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('custom_url')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('privacy_policy')
                        ->info('Privacy policy.')
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('custom_url')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('terms_of_use')
                        ->info('Terms of use.')
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('custom_url')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('trade_conditions')
                        ->info('General terms and conditions of trade.')
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('custom_url')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('cancellation_right_policy')
                        ->info('Cancellation right policy.')
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('custom_url')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('accessibility')
                        ->info('Accessibility statement.')
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('custom_url')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->integerNode('minimum_age')
                ->info('Minimum age permitted to register (0 disables the age check).')
                ->defaultValue(13)
                ->min(0)
                ->max(99)
            ->end()
        ->end()
    ;
};

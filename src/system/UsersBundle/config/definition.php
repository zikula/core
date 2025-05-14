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
        ->children()
            ->booleanNode('allow_self_deletion')
                ->info('Allow users to delete themselves.')
                ->defaultFalse()
            ->end()
            ->arrayNode('registration')
                ->info('Registration settings.')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')
                        ->info('Allow new user account registrations.')
                        ->defaultTrue()
                    ->end()
                    ->scalarNode('disabled_reason')
                        ->info('Statement displayed if registration disabled.')
                        ->defaultValue('Sorry! New user registration is currently disabled.')
                    ->end()
                    ->scalarNode('admin_notification_mail')
                        ->info('E-mail address to notify of registrations (leave blank for no notifications).')
                        ->defaultNull()
                        ->validate()
                            ->ifTrue(static function ($v) {
                                return null !== $v && !filter_var($v, FILTER_VALIDATE_EMAIL);
                            })
                            ->thenInvalid('Please enter a valid mail address.')
                        ->end()
                    ->end()
                ->end()
            ->end()
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
};

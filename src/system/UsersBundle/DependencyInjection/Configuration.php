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

namespace Zikula\UsersBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zikula_users');

        $treeBuilder->getRootNode()
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
            ->end()
        ;

        return $treeBuilder;
    }
}

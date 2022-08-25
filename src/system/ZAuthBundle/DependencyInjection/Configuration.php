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

namespace Zikula\ZAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zikula_z_auth');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('credentials')
                    ->info('User credential settings.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('minimum_password_length')
                            ->info('Minimum length for user passwords.')
                            ->defaultValue(8)
                            ->min(5)
                        ->end()
                        ->booleanNode('require_non_compromised_password')
                            ->info('Require non compromised passwords.')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('use_password_strength_meter')
                            ->info('Show password strength meter.')
                            ->defaultFalse()
                        ->end()
                        ->integerNode('change_email_expire_days')
                            ->info('The number of days a user\'s request to change e-mail addresses should be kept while waiting for verification (0 disables expiration). Changing this setting will affect all requests to change e-mail addresses currently pending verification.')
                            ->defaultValue(0)
                            ->min(0)
                        ->end()
                        ->integerNode('change_password_expire_days')
                            ->info('The number of days a user\'s request to reset a password should be kept while waiting for verification (0 disables expiration). This setting only affects users who have not established security question responses. Changing this setting will affect all password change requests currently pending verification.')
                            ->defaultValue(0)
                            ->min(0)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('registration')
                    ->info('Registration rettings. Must also be set in UsersBundle!')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('registration_expire_days')
                            ->info('The number of days a registration record should be kept while waiting for e-mail address verification. (Unverified registrations will be deleted the specified number of days after sending an e-mail verification message.) Enter zero (0) for no expiration (no automatic deletion). If registration is moderated and applications must be approved before verification, then registrations will not expire until the specified number of days after approval.')
                            ->defaultValue(0)
                            ->min(0)
                        ->end()
                        ->booleanNode('email_verification_required')
                            ->info('New users must verify their email address on registration.')
                            ->defaultTrue()
                        ->end()
                        ->arrayNode('antispam_question')
                            ->validate()
                                ->ifTrue(static function ($v) {
                                    return isset($v['question']) !== isset($v['answer']);
                                })
                                ->thenInvalid('Antispam needs both question and answer.')
                            ->end()
                            ->children()
                                ->scalarNode('question')
                                    ->info('Spam protection question to be answered at registration time, to protect the site against spam automated registrations by bots and scripts.')
                                    ->defaultNull()
                                ->end()
                                ->scalarNode('answer')
                                    ->info('Registering users will have to provide this response when answering the spam protection question.')
                                    ->defaultNull()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('login')
                    ->info('User login settings.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('display_inactive_status')
                            ->info('Failed login displays inactive status. If enabled, the log-in error message will indicate that the user account is inactive. If not, a generic error message is displayed.')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('display_pending_status')
                            ->info('Failed login displays verification status. If enabled, the log-in error message will indicate that the registration is pending verification or pending approval. If not, a generic error message is displayed.')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                ->integerNode('users_per_page')
                    ->info('Number of users displayed per page')
                    ->defaultValue(25)
                    ->min(1)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

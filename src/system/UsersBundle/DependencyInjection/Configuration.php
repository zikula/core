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
use Zikula\UsersBundle\UsersConstant;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zikula_users');

        $treeBuilder->getRootNode()
            ->children()
                ->integerNode('items_per_page')
                    ->info('Number of items (e.g., user account records) displayed per page.')
                    ->defaultValue(25)
                    ->min(1)
                    ->max(999)
                ->end()
                ->booleanNode('display_graphics_on_account_page')
                    ->info('Display graphics on user\'s account page.')
                    ->defaultTrue()
                ->end()
                ->booleanNode('allow_self_deletion')
                    ->info('Allow users to delete themselves (convert to ghost).')
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
                        ->booleanNode('moderation')
                            ->info('User registration is moderated (requires approval).')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('auto_login')
                            ->info('Newly registered users are logged in automatically (if admin approval (moderation) and e-mail verification are not required). Users authenticating off site (re-entrant) are logged in automatically regardless of this setting.')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('illegal_user_names')
                            ->info('Reserved user names. Separate each user name with a comma. Each user name on this list is not allowed to be chosen by someone registering for a new account.')
                            ->defaultValue('root, webmaster, admin, administrator, nobody, anonymous, username')
                            ->validate()
                                ->ifTrue(static function ($v) {
                                    $pattern = '/^(?:' . UsersConstant::UNAME_VALIDATION_PATTERN . '(?:\s*,\s*' . UsersConstant::UNAME_VALIDATION_PATTERN . ')*)?$/uD';

                                    return null !== $v && !preg_match($pattern, $v);
                                })
                                ->thenInvalid('The value provided does not appear to be a valid list of user names. The list should consist of one or more user names made up of lowercase letters, numbers, underscores, periods, or dashes. Separate each user name with a comma. For example: \'root, administrator, superuser\' (the quotes should not appear in the list). Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')
                            ->end()
                        ->end()
                        ->scalarNode('illegal_user_agents')
                            ->info('Banned user agents. Separate each user agent string with a comma. Each item on this list is a browser user agent identification string. If a user attempts to register a new account using a browser whose user agent string begins with one on this list, then the user is not allowed to begin the registration process.')
                            ->defaultNull()
                            ->validate()
                                ->ifTrue(static function ($v) {
                                    $pattern = '/^(?:[^\s,][^,]*(?:,\s?[^\s,][^,]*)*)?$/';

                                    return null !== $v && !preg_match($pattern, $v);
                                })
                                ->thenInvalid('The contents of this field does not appear to be a valid comma separated list. The list should consist of one or more string values separated by commas. For example: \'first example, 2nd example, tertiary example\' (the quotes should not appear in the list). One optional space following the comma is ignored for readability. Any other spaces (those appearing before the comma, and any additional spaces beyond the single optional space) will be considered to be part of the string value. Commas cannot be part of the string value. Empty values (two commas together, or separated only by a space) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')
                            ->end()
                        ->end()
                        ->scalarNode('illegal_domains')
                            ->info('Banned e-mail address domains. Separate each domain with a comma. Each item on this list is an e-mail address domain (the part after the \'@\'). E-mail addresses on new registrations or on an existing user\'s change of e-mail address requests are not allowed to have any domain on this list.')
                            ->defaultNull()
                            ->validate()
                                ->ifTrue(static function ($v) {
                                    $pattern = '/^(?:' . UsersConstant::EMAIL_DOMAIN_VALIDATION_PATTERN . '(?:\s*,\s*' . UsersConstant::EMAIL_DOMAIN_VALIDATION_PATTERN . ')*)?$/Ui';

                                    return null !== $v && !preg_match($pattern, $v);
                                })
                                ->thenInvalid('The contents of this field does not appear to be a valid list of e-mail address domains. The list should consist of one or more e-mail address domains (the part after the \'@\'), separated by commas. For example: \'gmail.com, example.org, acme.co.uk\' (the quotes should not appear in the list). Do not include the \'@\' itself. Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('integration')
                    ->info('Integration settings.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('profile_bundle')
                            ->info('Bundle used for user profile management (needs to implement ProfileBundleInterface).')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('message_bundle')
                            ->info('Bundle used for private messaging (needs to implement MessageBundleInterface).')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

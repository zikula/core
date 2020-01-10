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

namespace Zikula\UsersModule\Form\Type\ConfigType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Zikula\UsersModule\Constant as UsersConstant;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /**
             * General Settings
             */
            ->add(UsersConstant::MODVAR_ANONYMOUS_DISPLAY_NAME, TextType::class, [
                'label' => 'Name displayed for anonymous user',
                'required' => false,
                'help' => 'Anonymous users are visitors to your site who have not logged in.',
                'constraints' => [
                    new NotBlank(),
                    new Type('string')
                ]
            ])
            ->add(UsersConstant::MODVAR_ITEMS_PER_PAGE, IntegerType::class, [
                'label' => 'Number of users displayed per page',
                'help' => 'When lists are displayed (for example, lists of users, lists of registrations) this option controls how many items are displayed at one time.',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1])
                ]
            ])
            /**
             * Account Page Settings
             */
            ->add(UsersConstant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS, CheckboxType::class, [
                'label' => 'Display graphics on user\'s account page',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add(UsersConstant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH, TextType::class, [
                'label' => 'Path to account page images',
                'constraints' => [
                    new NotBlank(),
                    new Type('string')
                ]
            ])
            ->add(UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_PAGE, IntegerType::class, [
                'label' => 'Number of links per page',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1])
                ]
            ])
            ->add(UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_ROW, IntegerType::class, [
                'label' => 'Number of links per page',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1])
                ]
            ])
            /**
             * Registration Settings
             */
            ->add(UsersConstant::MODVAR_REGISTRATION_ENABLED, CheckboxType::class, [
                'label' => 'Allow new user account registrations',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_DISABLED_REASON, TextareaType::class, [
                'label' => 'Statement displayed if registration disabled',
                'required' => false,
                'constraints' => [
                    new Type('string')
                ]
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL, EmailType::class, [
                'label' => 'E-mail address to notify of registrations',
                'required' => false,
                'help' => 'A notification is sent to this e-mail address for each registration. Leave blank for no notifications.',
                'input_group' => ['left' => '<i class="fa fa-at"></i>'],
                'constraints' => [
                    new Type('string')
                ]
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, CheckboxType::class, [
                'label' => 'User registration is moderated',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'attr' => ['class' => 'registration-moderation-input']
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, CheckboxType::class, [
                'label' => 'Newly registered users are logged in automatically',
                'label_attr' => ['class' => 'switch-custom'],
                'help' => 'Users authenticating off site (re-entrant) are logged in automatically regardless of this setting.',
                'required' => false
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES, TextType::class, [
                'label' => 'Reserved user names',
                'required' => false,
                'help' => [
                    'Separate each user name with a comma.',
                    'Each user name on this list is not allowed to be chosen by someone registering for a new account.'
                ],
                'constraints' => [
                    new Type('string'),
                    new Regex([
                        'pattern' => '/^(?:' . UsersConstant::UNAME_VALIDATION_PATTERN . '(?:\s*,\s*' . UsersConstant::UNAME_VALIDATION_PATTERN . ')*)?$/uD',
                        'message' => 'The value provided does not appear to be a valid list of user names. The list should consist of one or more user names made up of lowercase letters, numbers, underscores, periods, or dashes. Separate each user name with a comma. For example: \'root, administrator, superuser\' (the quotes should not appear in the list). Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).'
                    ])
                ]
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS, TextareaType::class, [
                'label' => 'Banned user agents',
                'required' => false,
                'help' => 'Separate each user agent string with a comma. Each item on this list is a browser user agent identification string. If a user attempts to register a new account using a browser whose user agent string begins with one on this list, then the user is not allowed to begin the registration process.',
                'constraints' => [
                    new Type('string'),
                    new Regex([
                        'pattern' => '/^(?:[^\s,][^,]*(?:,\s?[^\s,][^,]*)*)?$/',
                        'message' => 'The contents of this field does not appear to be a valid comma separated list. The list should consist of one or more string values separated by commas. For example: \'first example, 2nd example, tertiary example\' (the quotes should not appear in the list). One optional space following the comma is ignored for readability. Any other spaces (those appearing before the comma, and any additional spaces beyond the single optional space) will be considered to be part of the string value. Commas cannot be part of the string value. Empty values (two commas together, or separated only by a space) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).'
                    ])
                ]
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS, TextareaType::class, [
                'label' => 'Banned e-mail address domains',
                'required' => false,
                'help' => [
                    'Separate each domain with a comma.',
                    'Each item on this list is an e-mail address domain (the part after the \'@\'). E-mail addresses on new registrations or on an existing user\'s change of e-mail address requests are not allowed to have any domain on this list.'
                    ],
                'constraints' => [
                    new Type('string'),
                    new Regex([
                        'pattern' => '/^(?:' . UsersConstant::EMAIL_DOMAIN_VALIDATION_PATTERN . '(?:\s*,\s*' . UsersConstant::EMAIL_DOMAIN_VALIDATION_PATTERN . ')*)?$/Ui',
                        'message' => 'The contents of this field does not appear to be a valid list of e-mail address domains. The list should consist of one or more e-mail address domains (the part after the \'@\'), separated by commas. For example: \'gmail.com, example.org, acme.co.uk\' (the quotes should not appear in the list). Do not include the \'@\' itself. Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).'
                    ])
                ]
            ])
            /**
             * User Login Settings
             */
            ->add(UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS, CheckboxType::class, [
                'label' => 'Failed login displays inactive status',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'help' => 'If checked, the log-in error message will indicate that the user account is inactive. If not, a generic error message is displayed.',
            ])
            ->add(UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS, CheckboxType::class, [
                'label' => 'Failed login displays verification status',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'help' => 'If checked, the log-in error message will indicate that the registration is pending verification. If not, a generic error message is displayed.',
            ])
            ->add(UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS, CheckboxType::class, [
                'label' => 'Failed login displays approval status',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'help' => 'If checked, the log-in error message will indicate that the registration is pending approval. If not, a generic error message is displayed.',
            ])
            /**
             * Buttons
             */
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_config';
    }
}

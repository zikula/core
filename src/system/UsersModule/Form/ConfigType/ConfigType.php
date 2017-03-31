<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\ConfigType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Zikula\UsersModule\Constant as UsersConstant;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /**
             * General Settings
             */
            ->add(UsersConstant::MODVAR_ANONYMOUS_DISPLAY_NAME, 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Name displayed for anonymous user'),
                'required' => false,
                'help' => $options['translator']->__('Anonymous users are visitors to your site who have not logged in.'),
                'constraints' => [
                    new NotBlank(),
                    new Type('string')
                ]
            ])
            ->add(UsersConstant::MODVAR_ITEMS_PER_PAGE, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('Number of users displayed per page'),
                'help' => $options['translator']->__('When lists are displayed (for example, lists of users, lists of registrations) this option controls how many items are displayed at one time.'),
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1])
                ]
            ])
            /**
             * Account Page Settings
             */
            ->add(UsersConstant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Display graphics on user\'s account page'),
                'required' => false,
            ])
            ->add(UsersConstant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH, 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Path to account page images'),
                'constraints' => [
                    new NotBlank(),
                    new Type('string')
                ]
            ])
            ->add(UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_PAGE, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('Number of links per page'),
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1])
                ]
            ])
            ->add(UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_ROW, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('Number of links per page'),
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1])
                ]
            ])
            /**
             * Registration Settings
             */
            ->add(UsersConstant::MODVAR_REGISTRATION_ENABLED, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Allow new user account registrations'),
                'required' => false,
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_DISABLED_REASON, 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $options['translator']->__('Statement displayed if registration disabled'),
                'required' => false,
                'constraints' => [
                    new Type('string')
                ]
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL, 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => $options['translator']->__('E-mail address to notify of registrations'),
                'required' => false,
                'help' => $options['translator']->__('A notification is sent to this e-mail address for each registration. Leave blank for no notifications.'),
                'input_group' => ['left' => '<i class="fa fa-at"></i>'],
                'constraints' => [
                    new Type('string')
                ]
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('User registration is moderated'),
                'required' => false,
                'attr' => ['class' => 'registration-moderation-input']
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Newly registered users are logged in automatically'),
                'required' => false,
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES, 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Reserved user names'),
                'required' => false,
                'help' => [
                    $options['translator']->__('Separate each user name with a comma.'),
                    $options['translator']->__('Each user name on this list is not allowed to be chosen by someone registering for a new account.')
                    ],
                'constraints' => [
                    new Type('string'),
                    new Regex([
                        'pattern' => '/^(?:'. UsersConstant::UNAME_VALIDATION_PATTERN .'(?:\s*,\s*'. UsersConstant::UNAME_VALIDATION_PATTERN .')*)?$/uD',
                        'message' => $options['translator']->__('The value provided does not appear to be a valid list of user names. The list should consist of one or more user names made up of lowercase letters, numbers, underscores, periods, or dashes. Separate each user name with a comma. For example: \'root, administrator, superuser\' (the quotes should not appear in the list). Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')
                    ])
                ]
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS, 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $options['translator']->__('Banned user agents'),
                'required' => false,
                'help' => $options['translator']->__('Separate each user agent string with a comma. Each item on this list is a browser user agent identification string. If a user attempts to register a new account using a browser whose user agent string begins with one on this list, then the user is not allowed to begin the registration process.'),
                'constraints' => [
                    new Type('string'),
                    new Regex([
                        'pattern' => '/^(?:[^\s,][^,]*(?:,\s?[^\s,][^,]*)*)?$/',
                        'message' => $options['translator']->__('The contents of this field does not appear to be a valid comma separated list. The list should consist of one or more string values separated by commas. For example: \'first example, 2nd example, tertiary example\' (the quotes should not appear in the list). One optional space following the comma is ignored for readability. Any other spaces (those appearing before the comma, and any additional spaces beyond the single optional space) will be considered to be part of the string value. Commas cannot be part of the string value. Empty values (two commas together, or separated only by a space) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')
                    ])
                ]
            ])
            ->add(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS, 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $options['translator']->__('Banned e-mail address domains'),
                'required' => false,
                'help' => [
                    $options['translator']->__('Separate each domain with a comma.'),
                    $options['translator']->__('Each item on this list is an e-mail address domain (the part after the \'@\'). E-mail addresses on new registrations or on an existing user\'s change of e-mail address requests are not allowed to have any domain on this list.')
                    ],
                'constraints' => [
                    new Type('string'),
                    new Regex([
                        'pattern' => '/^(?:'. UsersConstant::EMAIL_DOMAIN_VALIDATION_PATTERN .'(?:\s*,\s*'. UsersConstant::EMAIL_DOMAIN_VALIDATION_PATTERN .')*)?$/Ui',
                        'message' => $options['translator']->__('The contents of this field does not appear to be a valid list of e-mail address domains. The list should consist of one or more e-mail address domains (the part after the \'@\'), separated by commas. For example: \'gmail.com, example.org, acme.co.uk\' (the quotes should not appear in the list). Do not include the \'@\' itself. Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')
                    ])
                ]
            ])
            /**
             * User Login Settings
             */
            ->add(UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Failed login displays inactive status'),
                'required' => false,
                'help' => $options['translator']->__('If checked, the log-in error message will indicate that the user account is inactive. If not, a generic error message is displayed.'),
            ])
            ->add(UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Failed login displays verification status'),
                'required' => false,
                'help' => $options['translator']->__('If checked, the log-in error message will indicate that the registration is pending verification. If not, a generic error message is displayed.'),
            ])
            ->add(UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Failed login displays approval status'),
                'required' => false,
                'help' => $options['translator']->__('If checked, the log-in error message will indicate that the registration is pending approval. If not, a generic error message is displayed.'),
            ])
            /**
             * Buttons
             */
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulausersmodule_config';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}

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

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Zikula\ZAuthModule\ZAuthConstant;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, IntegerType::class, [
                'label' => 'Minimum length for user passwords',
                'required' => false,
                'help' => 'This affects both passwords created during registration, as well as passwords modified by users or administrators. Enter an integer greater than %number%.',
                'help_translation_parameters' => [
                    '%number%' => ZAuthConstant::PASSWORD_MINIMUM_LENGTH
                ],
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => ZAuthConstant::PASSWORD_MINIMUM_LENGTH])
                ]
            ])
            ->add(ZAuthConstant::MODVAR_REQUIRE_NON_COMPROMISED_PASSWORD, CheckboxType::class, [
                'label' => 'Require non compromised passwords',
                'help' => 'Applies to ALL passwords (including admins). See %symfonydocs%',
                'help_translation_parameters' => [
                    '%symfonydocs%' => '<a target="_blank" href="https://symfony.com/doc/current/reference/constraints/NotCompromisedPassword.html">Symfony docs</a>'
                ],
                'help_html' => true,
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
            ])
            ->add(ZAuthConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED, CheckboxType::class, [
                'label' => 'Show password strength meter',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
            ])
            ->add(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL, IntegerType::class, [
                'label' => 'Email address verifications expire in',
                'help' => 'Enter the number of days a user\'s request to change e-mail addresses should be kept while waiting for verification. Enter zero (0) for no expiration.',
                'input_group' => ['right' => 'days'],
                'alert' => [
                    'Changing this setting will affect all requests to change e-mail addresses currently pending verification.' => 'warning'
                ],
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 0])
                ]
            ])
            ->add(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, IntegerType::class, [
                'label' => 'Password reset requests expire in',
                'help' => 'This setting only affects users who have not established security question responses. Enter the number of days a user\'s request to reset a password should be kept while waiting for verification. Enter zero (0) for no expiration.',
                'input_group' => ['right' => 'days'],
                'alert' => [
                    'Changing this setting will affect all password change requests currently pending verification.' => 'warning'
                ],
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 0])
                ]
            ])
            ->add(ZAuthConstant::MODVAR_EXPIRE_DAYS_REGISTRATION, IntegerType::class, [
                'label' => 'Registrations pending verification expire in',
                'help' => 'Enter the number of days a registration record should be kept while waiting for e-mail address verification. (Unverified registrations will be deleted the specified number of days after sending an e-mail verification message.) Enter zero (0) for no expiration (no automatic deletion).',
                'input_group' => ['right' => 'days'],
                'alert' => [
                    'If registration is moderated and applications must be approved before verification, then registrations will not expire until the specified number of days after approval.' => 'info',
                    'Changing this setting will affect all password change requests currently pending verification.' => 'warning'
                ],
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 0])
                ]
            ])
            ->add(ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED, CheckboxType::class, [
                'label' => 'New users must verify their email address on registration.',
                'label_attr' => ['class' => 'switch-custom'],
                //'help' => 'Users created by an admin are automatically considered verified.',
                'required' => false,
            ])
            ->add(ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION, TextType::class, [
                'label' => 'Spam protection question',
                'required' => false,
                'help' => 'You can set a question to be answered at registration time, to protect the site against spam automated registrations by bots and scripts.',
                'constraints' => [
                    new Type('string')
                ]
            ])
            ->add(ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER, TextType::class, [
                'label' => 'Spam protection answer',
                'required' => false,
                'help' => 'Registering users will have to provide this response when answering the spam protection question. It is required if a spam protection question is provided.',
                'constraints' => [
                    new Type('string')
                ]
            ])
            ->add(ZAuthConstant::MODVAR_ITEMS_PER_PAGE, IntegerType::class, [
                'label' => 'Number of users displayed per page',
                'help' => 'When lists are displayed (for example, lists of users, lists of registrations) this option controls how many items are displayed at one time.',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 1])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times'
            ])
            /**
             * Form Listeners
             */
            ->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $event) {
                $data = $event->getData();
                // clear anti-spam answer if there is no question
                if (empty($data[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION])) {
                    $data[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER] = '';
                }
                $event->setData($data);
            })
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new Callback([
                    'callback' => static function ($data, ExecutionContextInterface $context) {
                        if (!empty($data[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION]) && empty($data[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER])) {
                            $context
                                ->buildViolation('If a spam protection question is provided, then a spam protection answer must also be provided.')
                                ->atPath(ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER)
                                ->addViolation()
                            ;
                        }
                    }
                ]),
            ]
        ]);
    }
}

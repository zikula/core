<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
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
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('Minimum length for user passwords'),
                'required' => false,
                'help' => $options['translator']->__('This affects both passwords created during registration, as well as passwords modified by users or administrators. Enter an integer greater than zero.'),
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 3]) // @todo
                ]
            ])
            ->add(ZAuthConstant::MODVAR_HASH_METHOD, 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('Password hashing method'),
                'help' => $options['translator']->__('The default hashing method is \'SHA256\'.'), //@todo
                'choices' => [
                    'SHA1'  => 'sha1',
                    'SHA256' => 'sha256',
                    // @todo bcrypt
                ],
                'choices_as_values' => true
            ])
            ->add(ZAuthConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Show password strength meter'),
                'required' => false,
            ])
            ->add(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('E-mail address verifications expire in'),
                'help' => $options['translator']->__('Enter the number of days a user\'s request to change e-mail addresses should be kept while waiting for verification. Enter zero (0) for no expiration.'),
                'input_group' => ['right' => $options['translator']->__('days')],
                'alert' => [
                    $options['translator']->__('Changing this setting will affect all requests to change e-mail addresses currently pending verification.') => 'warning'
                ],
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 0])
                ]
            ])
            ->add(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('Password reset requests expire in'),
                'help' => $options['translator']->__('This setting only affects users who have not established security question responses. Enter the number of days a user\'s request to reset a password should be kept while waiting for verification. Enter zero (0) for no expiration.'),
                'input_group' => ['right' => $options['translator']->__('days')],
                'alert' => [
                    $options['translator']->__('Changing this setting will affect all password change requests currently pending verification.') => 'warning'
                ],
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 0])
                ]
            ])
            ->add(ZAuthConstant::MODVAR_EXPIRE_DAYS_REGISTRATION, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('Registrations pending verification expire in'),
                'help' => $options['translator']->__('Enter the number of days a registration record should be kept while waiting for e-mail address verification. (Unverified registrations will be deleted the specified number of days after sending an e-mail verification message.) Enter zero (0) for no expiration (no automatic deletion).'),
                'input_group' => ['right' => $options['translator']->__('days')],
                'alert' => [
                    $options['translator']->__('If registration is moderated and applications must be approved before verification, then registrations will not expire until the specified number of days after approval.') => 'info',
                    $options['translator']->__('Changing this setting will affect all password change requests currently pending verification.') => 'warning'
                ],
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 0])
                ]
            ])
            ->add(ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('New users must verify their email address on registration.'),
                'help' => $options['translator']->__('Users created by an admin are automatically considered verified.'),
                'required' => false,
            ])
            ->add(ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION, 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Spam protection question'),
                'required' => false,
                'help' => $options['translator']->__('You can set a question to be answered at registration time, to protect the site against spam automated registrations by bots and scripts.'),
                'constraints' => [
                    new Type('string')
                ]
            ])
            ->add(ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER, 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Spam protection answer'),
                'required' => false,
                'help' => $options['translator']->__('Registering users will have to provide this response when answering the spam protection question. It is required if a spam protection question is provided.'),
                'constraints' => [
                    new Type('string')
                ]
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
            /**
             * Form Listeners
             */
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                // clear anti-spam answer if there is no question
                if (empty($data[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION])) {
                    $data[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER] = '';
                }
                $event->setData($data);
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_config';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'constraints' => [
                new Callback([
                    'callback' => function ($data, ExecutionContextInterface $context) {
                        if (!empty($data[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION]) && empty($data[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER])) {
                            $context->buildViolation('If a spam protection question is provided, then a spam protection answer must also be provided.')
                                ->atPath(ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER)
                                ->addViolation();
                        }
                    }
                ]),
            ]
        ]);
    }
}

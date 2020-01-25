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

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\Validator\Constraints\ValidUserFields;
use Zikula\ZAuthModule\ZAuthConstant;

class AdminCreatedUserType extends AbstractType
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(VariableApiInterface $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('method', ChoiceType::class, [
                'label' => 'Login method',
                'choices' => [
                    'User name or email' => ZAuthConstant::AUTHENTICATION_METHOD_EITHER,
                    'User name' => ZAuthConstant::AUTHENTICATION_METHOD_UNAME,
                    'Email' => ZAuthConstant::AUTHENTICATION_METHOD_EMAIL
                ]
            ])
            ->add('uname', TextType::class, [
                'label' => 'User name',
                'help' => 'User names can contain letters, numbers, underscores, periods, spaces and/or dashes.',
                'constraints' => [
                    new ValidUname()
                ]
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => 'Email',
                    'help' => 'If login method is Email, then this value must be unique for the site.',
                ],
                'second_options' => ['label' => 'Repeat Email'],
                'invalid_message' => 'The emails must match!',
                'constraints' => [
                    new ValidEmail()
                ]
            ])
            ->add('setpass', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Set password now',
                'label_attr' => ['class' => 'switch-custom'],
                'alert' => ['If unchecked, the user\'s e-mail address will be verified. The user will create a password at that time.' => 'info']
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Create new password',
                    'input_group' => ['left' => '<i class="fas fa-asterisk"></i>'],
                    'help' => 'Minimum password length: %amount% characters.',
                    'help_translation_parameters' => [
                        '%amount%' => $options['minimumPasswordLength']
                    ]
                ],
                'second_options' => [
                    'label' => 'Repeat new password',
                    'input_group' => ['left' => '<i class="fas fa-asterisk"></i>']
                ],
                'invalid_message' => 'The passwords must match!',
                'constraints' => [
                    new ValidPassword()
                ]
            ])
            ->add('sendpass', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Send password via email',
                'label_attr' => ['class' => 'switch-custom'],
                'alert' => [
                    'Sending a password via email is considered unsafe. It is recommended that you provide the password to the user using a secure method of communication.' => 'warning',
                    'Even if you choose to not send the user\'s password via e-mail, other email messages may be sent to the user as part of the registration process.' => 'info'
                ]
            ])
            ->add('usernotification', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Send welcome message to user',
                'label_attr' => ['class' => 'switch-custom']
            ])
            ->add('adminnotification', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Notify administrators',
                'label_attr' => ['class' => 'switch-custom']
            ])
            ->add('usermustverify', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'data' => $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED, ZAuthConstant::DEFAULT_EMAIL_VERIFICATION_REQUIRED),
                'label' => 'User must verify email address',
                'label_attr' => ['class' => 'switch-custom'],
                'help' => 'Notice: This overrides the \'Verify e-mail address during registration\' setting in \'Settings\'.',
                'alert' => ['It is recommended to force users to verify their email address.' => 'info']
            ])
            ->add('submit', SubmitType::class, [
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
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_admincreateduser';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'minimumPasswordLength' => 5,
            'constraints' => [
                new ValidUserFields()
            ]
        ]);
    }
}

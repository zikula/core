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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\Validator\Constraints\ValidUserFields;

class AdminModifyUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                ],
                'second_options' => [
                    'label' => 'Repeat email'
                ],
                'invalid_message' => 'The emails must match!',
                'constraints' => [
                    new ValidEmail()
                ]
            ])
            ->add('setpass', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Set password now',
                'label_attr' => ['class' => 'switch-custom']
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'required' => false,
                    'label' => 'Create new password',
                    'input_group' => ['left' => '<i class="fas fa-asterisk"></i>'],
                    'help' => 'Minimum password length: %amount% characters.',
                    'help_translation_parameters' => [
                        '%amount%' => $options['minimumPasswordLength']
                    ]
                ],
                'second_options' => [
                    'required' => false,
                    'label' => 'Repeat new password',
                    'input_group' => ['left' => '<i class="fas fa-asterisk"></i>']
                ],
                'invalid_message' => 'The passwords must match!',
                'constraints' => [
                    new ValidPassword()
                ]
            ])
            ->add('submit', SubmitType::class, [
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
        return 'zikulazauthmodule_adminmodifyuser';
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

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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\Validator\Constraints\ValidPasswordChange;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uid', HiddenType::class)
            ->add('login', HiddenType::class)
            ->add('authenticationMethod', HiddenType::class)
            ->add('oldpass', PasswordType::class, [
                'required' => false,
                'label' => 'Old password',
                'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'New password',
                    'help' => 'Minimum password length: %amount% characters.',
                    'help_translation_parameters' => [
                        '%amount%' => $options['minimumPasswordLength']
                    ]
                ],
                'second_options' => [
                    'label' => 'Repeat new password'
                ],
                'invalid_message' => 'The passwords must match!',
                'constraints' => [
                    new NotNull(),
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
        return 'zikulazauthmodule_changepassword';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'minimumPasswordLength' => 5,
            'constraints' => [
                new ValidPasswordChange()
            ]
        ]);
    }
}

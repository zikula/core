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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
use Zikula\ZAuthModule\ZAuthConstant;

class AdminCreatedUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('method', ChoiceType::class, [
                'label' => $options['translator']->__('Login method'),
                'choices' => [
                    $options['translator']->__('User name or email') => ZAuthConstant::AUTHENTICATION_METHOD_EITHER,
                    $options['translator']->__('User name') => ZAuthConstant::AUTHENTICATION_METHOD_UNAME,
                    $options['translator']->__('Email') => ZAuthConstant::AUTHENTICATION_METHOD_EMAIL
                ]
            ])
            ->add('uname', TextType::class, [
                'label' => $options['translator']->__('User name'),
                'help' => $options['translator']->__('User names can contain letters, numbers, underscores, periods, spaces and/or dashes.'),
                'constraints' => [new ValidUname()]
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => $options['translator']->__('Email'),
                    'help' => $options['translator']->__('If login method is Email, then this value must be unique for the site.'),
                ],
                'second_options' => ['label' => $options['translator']->__('Repeat Email')],
                'invalid_message' => $options['translator']->__('The emails  must match!'),
                'constraints' => [new ValidEmail()]
            ])
            ->add('setpass', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => $options['translator']->__('Set password now'),
                'alert' => [$options['translator']->__('If unchecked, the user\'s e-mail address will be verified. The user will create a password at that time.') => 'info']
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => $options['translator']->__('Create new password'),
                    'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
                ],
                'second_options' => [
                    'label' => $options['translator']->__('Repeat new password'),
                    'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
                ],
                'invalid_message' => $options['translator']->__('The passwords must match!'),
                'constraints' => [
                    new ValidPassword(),
                ]
            ])
            ->add('sendpass', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => $options['translator']->__('Send password via email'),
                'alert' => [
                    $options['translator']->__('Sending a password via e-mail is considered unsafe. It is recommended that you provide the password to the user using a secure method of communication.') => 'warning',
                    $options['translator']->__('Even if you choose to not send the user\'s password via e-mail, other e-mail messages may be sent to the user as part of the registration process.') => 'info'
                ]
            ])
            ->add('usernotification', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => $options['translator']->__('Send welcome message to user'),
            ])
            ->add('adminnotification', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => $options['translator']->__('Notify administrators'),
            ])
            ->add('usermustverify', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => $options['translator']->__('User must verify email address'),
                'help' => $options['translator']->__('Notice: This overrides the \'Verify e-mail address during registration\' setting in \'Settings\'.'),
                'alert' => [$options['translator']->__('It is recommended to force users to verify their email address.') => 'info']
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['translator']->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_admincreateduser';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'constraints' => [
                new ValidUserFields()
            ]
        ]);
    }
}

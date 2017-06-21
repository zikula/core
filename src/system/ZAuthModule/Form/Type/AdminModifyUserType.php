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
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => $options['translator']->__('User name'),
                'help' => $options['translator']->__('User names can contain letters, numbers, underscores, periods, spaces and/or dashes.'),
                'constraints' => [new ValidUname()]
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => $options['translator']->__('Email'),
                ],
                'second_options' => ['label' => $options['translator']->__('Repeat Email')],
                'invalid_message' => $options['translator']->__('The emails  must match!'),
                'constraints' => [new ValidEmail()]
            ])
            ->add('setpass', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => $options['translator']->__('Set password now'),
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'required' => false,
                    'label' => $options['translator']->__('Create new password'),
                    'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
                ],
                'second_options' => [
                    'required' => false,
                    'label' => $options['translator']->__('Repeat new password'),
                    'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
                ],
                'invalid_message' => $options['translator']->__('The passwords must match!'),
                'constraints' => [
                    new ValidPassword(),
                ]
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_adminmodifyuser';
    }

    /**
     * {@inheritdoc}
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

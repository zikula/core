<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Zikula\UsersModule\Constant as UsersConstant;

class CreateAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => __('Admin User Name'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'data' => __('admin'),
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5]),
                    new Regex([
                        'pattern' => '#' . UsersConstant::UNAME_VALIDATION_PATTERN . '#',
                        'message' => __('Error! Usernames can only consist of a combination of letters, numbers and may only contain the symbols . and _')
                    ])
                ]
            ])
            ->add('password', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => [
                    'label_attr' => [
                        'class' => 'col-sm-3'
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 7, 'max' => 40])
                    ]
                ],
                'required' => true,
                'first_options'  => ['label' => __('Admin Password')],
                'second_options' => ['label' => __('Repeat Password')]
            ])
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => __('Admin Email Address'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'createadmin';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
//                'csrf_field_name' => '_token',
//                // a unique key to help generate the secret token
//                'intention'       => '_zk_bdcreds',
        ]);
    }
}

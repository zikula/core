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

namespace Zikula\Bundle\CoreInstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Zikula\UsersModule\Constant as UsersConstant;

class CreateAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Admin user name',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'data' => 'admin',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5]),
                    new Regex([
                        'pattern' => '#' . UsersConstant::UNAME_VALIDATION_PATTERN . '#',
                        'message' => 'Error! Usernames can only consist of a combination of letters, numbers and may only contain the symbols . and _'
                    ])
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => [
                    'label_attr' => [
                        'class' => 'col-md-3'
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 7, 'max' => 40])
                    ]
                ],
                'required' => true,
                'first_options'  => ['label' => 'Admin password'],
                'second_options' => ['label' => 'Repeat password']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Admin email address',
                'label_attr' => [
                    'class' => 'col-md-3'
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
}

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

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Zikula\Bundle\CoreInstallerBundle\Form\AbstractType;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\UsersModule\Constant as UsersConstant;

class CreateAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setTranslator($options['translator']);
        $builder
            ->add('username', TextType::class, [
                'label' => $this->__('Admin User Name'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'data' => $this->__('admin'),
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5]),
                    new Regex([
                        'pattern' => '#' . UsersConstant::UNAME_VALIDATION_PATTERN . '#',
                        'message' => $this->__('Error! Usernames can only consist of a combination of letters, numbers and may only contain the symbols . and _')
                    ])
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => 'password',
                'invalid_message' => $this->__('The password fields must match.'),
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
                'first_options'  => ['label' => $this->__('Admin Password')],
                'second_options' => ['label' => $this->__('Repeat Password')]
            ])
            ->add('email', EmailType::class, [
                'label' => $this->__('Admin Email Address'),
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
            'translator' => new IdentityTranslator()
//                'csrf_field_name' => '_token',
//                // a unique key to help generate the secret token
//                'intention'       => '_zk_bdcreds',
        ]);
    }
}

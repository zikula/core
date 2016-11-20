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

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\Bundle\CoreInstallerBundle\Form\AbstractType;
use Zikula\Bundle\CoreInstallerBundle\Validator\Constraints\AuthenticateAdminLogin;
use Zikula\Common\Translator\IdentityTranslator;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setTranslator($options['translator']);
        $builder
            ->add('username', TextType::class, [
                'label' => $this->__('User Name'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'data' => $this->__('admin'),
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => $this->__('Password'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'login';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => new AuthenticateAdminLogin(),
            'csrf_protection' => false,
            'translator' => new IdentityTranslator()
//                'csrf_field_name' => '_token',
//                // a unique key to help generate the secret token
//                'intention'       => '_zk_bdcreds',
        ]);
    }
}

<?php
/**
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
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => __('User Name'),
                'label_attr' => [
                    'class' => 'col-sm-3'
                ],
                'data' => __('admin'),
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('password', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => __('Password'),
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
            'constraints' => new Callback(['callback' => ['Zikula\Bundle\CoreInstallerBundle\Validator\CoreInstallerValidator', 'validateAndLogin']]),
            'csrf_protection' => false,
//                'csrf_field_name' => '_token',
//                // a unique key to help generate the secret token
//                'intention'       => '_zk_bdcreds',
        ]);
    }
}

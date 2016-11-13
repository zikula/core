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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\Validator\Constraints\ValidPasswordChange;
use Zikula\ZAuthModule\ZAuthConstant;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('authenticationMethod', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('oldpass', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'required' => false,
                'label' => $options['translator']->__('Old password'),
                'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
            ])
            ->add('pass', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                'first_options' => ['label' => $options['translator']->__('New password')],
                'second_options' => ['label' => $options['translator']->__('Repeat new password')],
                'invalid_message' => $options['translator']->__('The passwords must match!'),
                'constraints' => [
                    new NotNull(),
                    new ValidPassword()
                ]
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_changepassword';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'constraints' => [
                new ValidPasswordChange()
            ]
        ]);
    }
}

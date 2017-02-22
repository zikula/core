<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\ConfigType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthenticationMethodsType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('authenticationMethodsStatus', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'entry_type' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                'required' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success'],
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    /**
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulausersmodule_authenticationmenthodselector';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermissionCheckType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('user', TextType::class, [
                'label' => $translator->__('User name'),
                'required' => false
            ])
            ->add('component', TextType::class, [
                'label' => $translator->__('Component to check'),
                'data' => '.*'
            ])
            ->add('instance', TextType::class, [
                'label' => $translator->__('Instance to check'),
                'data' => '.*'
            ])
            ->add('level', ChoiceType::class, [
                'label' => $translator->__('Permission level'),
                'choices' => array_flip($options['permissionLevels']),
                'data' => ACCESS_READ
            ])
            ->add('check', ButtonType::class, [
                'label' => $translator->__('Check permission'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->add('reset', ButtonType::class, [
                'label' => $translator->__('Reset'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-danger'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulapermissionsmodule_permissioncheck';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['id' => 'testpermform'],
            'translator' => null,
            'permissionLevels' => []
        ]);
    }
}

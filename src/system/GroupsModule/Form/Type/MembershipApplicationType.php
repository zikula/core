<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Membership application form type class.
 */
class MembershipApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('group', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'property_path' => 'group.gid'
            ])
            ->add('user', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'property_path' => 'user.uid'
            ])
            ->add('status', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('application', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Comment to attach to your application'),
                'required' => false
            ])
            ->add('apply', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Apply'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulagroupsmodule_membershipapplication';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}

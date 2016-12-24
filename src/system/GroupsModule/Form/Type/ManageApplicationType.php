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
 * Application management form type class.
 */
class ManageApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('gid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('userid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('theAction', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('userName', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('User name'),
                'empty_data' => '',
                'required' => false,
                'disabled' => true
            ])
            ->add('application', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Membership application'),
                'empty_data' => '',
                'required' => false,
                'disabled' => true
            ])
        ;
        if ($options['data']['theAction'] == 'deny') {
            $builder->add('reason', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Reason'),
                'empty_data' => '',
                'required' => false
            ]);
        }

        $builder
            ->add('sendtag', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Notification type'),
                'empty_data' => 0,
                'choices' => [
                    $translator->__('None') => 0,
                    $translator->__('E-mail') => 1
                ],
                'choices_as_values' => true,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['theAction'] == 'deny' ? $translator->__('Deny') : $translator->__('Accept'),
                'icon' => $options['theAction'] == 'deny' ? 'fa-user-times' : 'fa-user-plus',
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
        return 'zikulagroupsmodule_manageapplication';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'theAction' => 'accept'
        ]);
        $resolver->setAllowedValues('theAction', ['accept', 'deny']);
    }
}

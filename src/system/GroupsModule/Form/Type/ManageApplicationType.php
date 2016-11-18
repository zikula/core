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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
            ->add('gid', HiddenType::class)
            ->add('userid', HiddenType::class)
            ->add('theAction', HiddenType::class)
            ->add('userName', TextType::class, [
                'label' => $translator->__('User name'),
                'empty_data' => '',
                'required' => false,
                'disabled' => true
            ])
            ->add('application', TextType::class, [
                'label' => $translator->__('Membership application'),
                'empty_data' => '',
                'required' => false,
                'disabled' => true
            ])
        ;

        if ($options['theAction'] == 'deny') {
            $builder->add('reason', TextareaType::class, [
                'label' => $translator->__('Reason'),
                'empty_data' => '',
                'required' => false
            ]);
        }

        $builder
            ->add('sendtag', ChoiceType::class, [
                'label' => $translator->__('Notification type'),
                'empty_data' => 0,
                'choices' => [
                    $translator->__('None') => 0,
                    $translator->__('E-mail') => 1
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('apply', SubmitType::class, [
                'label' => $options['theAction'] == 'deny' ? $translator->__('Deny') : $translator->__('Accept'),
                'icon' => $options['theAction'] == 'deny' ? 'fa-user-times' : 'fa-user-plus',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
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

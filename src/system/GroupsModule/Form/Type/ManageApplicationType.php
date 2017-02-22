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
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];
        $reason = $options['data']['theAction'] == 'accept'
            ? $translator->__('Congratulations! Your group application has been accepted. You have been granted all the privileges assigned to the group of which you are now member.')
            : $translator->__('Sorry! This is a message to inform you with regret that your application for membership of the requested private group has been rejected.');
        $builder
            ->add('theAction', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('application', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'property_path' => '[application].app_id'
            ])
            ->add('reason', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Email content'),
                'data' => $reason,
                'required' => false
            ])
            ->add('sendtag', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Notification type'),
                'data' => 1,
                'choices' => [
                    $translator->__('None') => 0,
                    $translator->__('E-mail') => 1
                ],
                'choices_as_values' => true,
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['data']['theAction'] == 'deny' ? $translator->__('Deny') : $translator->__('Accept'),
                'icon' => $options['data']['theAction'] == 'deny' ? 'fa-user-times' : 'fa-user-plus',
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
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulagroupsmodule_manageapplication';
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

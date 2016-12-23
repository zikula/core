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
use Zikula\GroupsModule\Helper\CommonHelper;

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
            ->add('gid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [])
            ->add('theAction', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [])
            ->add('groupType', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [])
            ->add('groupName', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Group name'),
                'empty_data' => $translator->__('Not available'),
                'required' => false,
                'disabled' => true
            ])
            ->add('groupDescription', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Description'),
                'empty_data' => $translator->__('Not available'),
                'required' => false,
                'disabled' => true
            ])
        ;

        if ($options['theAction'] == 'subscribe' && $options['groupType'] == CommonHelper::GTYPE_PRIVATE) {
            $builder->add('applyText', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Comment to attach to your application'),
                'empty_data' => '',
                'required' => false
            ]);
        }

        $builder
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
            'translator' => null,
            'theAction' => 'subscribe',
            'groupType' => 0
        ]);
        $resolver->setAllowedValues('theAction', ['subscribe', 'unsubscribe', 'cancel']);
    }
}

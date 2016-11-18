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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
            ->add('gid', HiddenType::class)
            ->add('theAction', HiddenType::class)
            ->add('groupType', HiddenType::class)
            ->add('groupName', TextType::class, [
                'label' => $translator->__('Group name'),
                'empty_data' => $translator->__('Not available'),
                'required' => false,
                'disabled' => true
            ])
            ->add('groupDescription', TextareaType::class, [
                'label' => $translator->__('Description'),
                'empty_data' => $translator->__('Not available'),
                'required' => false,
                'disabled' => true
            ])
        ;

        if ($options['theAction'] == 'subscribe' && $options['groupType'] == 2) {
            $builder->add('applyText', TextareaType::class, [
                'label' => $translator->__('Comment to attach to your application'),
                'empty_data' => '',
                'required' => false
            ]);
        }

        $builder
            ->add('apply', SubmitType::class, [
                'label' => $translator->__('Apply'),
                'icon' => 'fa-check',
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
        return 'zikulagroupsmodule_membershipapplication';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
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

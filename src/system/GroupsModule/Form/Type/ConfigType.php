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
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('itemsperpage', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Items per page'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 3,
                    'min' => 1
                ]
            ])
            ->add('defaultgroup', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Initial user group'),
                'choices' => $options['groups'],
                'choices_as_values' => true,
            ])
            ->add('hideclosed', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Hide closed groups'),
                'required' => false
            ])
            ->add('hidePrivate', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Hide private groups'),
                'required' => false
            ])
            ->add('mailwarning', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Receive e-mail alert when there are new applicants'),
                'required' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save'),
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
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulagroupsmodule_config';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'groups' => []
        ]);
    }
}

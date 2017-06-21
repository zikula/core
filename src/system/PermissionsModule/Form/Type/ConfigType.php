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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $amountChoices = [
            20 => 20,
            25 => 25,
            30 => 30,
            35 => 35,
            40 => 40
        ];

        $builder
            ->add('lockadmin', CheckboxType::class, [
                'label' => $translator->__('Lock main administration permission rule'),
                'required' => false
            ])
            ->add('adminid', IntegerType::class, [
                'label' => $translator->__('ID of main administration permission rule'),
                'empty_data' => 1,
                'scale' => 0,
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('filter', CheckboxType::class, [
                'label' => $translator->__('Enable filtering of group permissions'),
                'required' => false
            ])
            ->add('rowview', ChoiceType::class, [
                'label' => $translator->__('Minimum row height for permission rules list view (in pixels)'),
                'empty_data' => 25,
                'choices' => $amountChoices,
                'choices_as_values' => true
            ])
            ->add('rowedit', ChoiceType::class, [
                'label' => $translator->__('Minimum row height for rule editing view (in pixels)'),
                'empty_data' => 35,
                'choices' => $amountChoices,
                'choices_as_values' => true
            ])
            ->add('save', SubmitType::class, [
                'label' => $translator->__('Save'),
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
        return 'zikulapermissionsmodule_config';
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

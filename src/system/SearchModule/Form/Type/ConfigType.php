<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Form\Type;

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
            ->add('limitsummary', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Number of characters to display in item summaries'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 5
                ]
            ])
            ->add('plugins', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Disabled plugins'),
                'label_attr' => ['class' => 'checkbox-inline'],
                'choices' => $options['plugins'],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => true,
                'required' => false
            ])
            ->add('opensearch_enabled', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Enable OpenSearch'),
                'required' => false
            ])
            ->add('opensearch_adult_content', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('This page contains adult content'),
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
        return 'zikulasearchmodule_config';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'plugins' => []
        ]);
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemsperpage', IntegerType::class, [
                'label' => 'Items per page',
                'attr' => [
                    'maxlength' => 3,
                    'min' => 1
                ]
            ])
            ->add('limitsummary', IntegerType::class, [
                'label' => 'Number of characters to display in item summaries',
                'attr' => [
                    'maxlength' => 5
                ]
            ])
            ->add('plugins', ChoiceType::class, [
                'label' => 'Disabled plugins',
                'label_attr' => ['class' => 'checkbox-custom'],
                'choices' => /** @Ignore */$options['plugins'],
                'expanded' => true,
                'multiple' => true,
                'required' => false
            ])
            ->add('opensearch_enabled', CheckboxType::class, [
                'label' => 'Enable OpenSearch',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('opensearch_adult_content', CheckboxType::class, [
                'label' => 'This page contains adult content',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times'
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulasearchmodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'plugins' => []
        ]);
    }
}

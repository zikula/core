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

namespace Zikula\PermissionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $amountChoices = [
            20 => 20,
            25 => 25,
            30 => 30,
            35 => 35,
            40 => 40
        ];

        $builder
            ->add('lockadmin', CheckboxType::class, [
                'label' => 'Lock main administration permission rule',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('adminid', IntegerType::class, [
                'label' => 'ID of main administration permission rule',
                'empty_data' => 1,
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('filter', CheckboxType::class, [
                'label' => 'Enable filtering of group permissions',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('rowview', ChoiceType::class, [
                'label' => 'Minimum row height for permission rules list view',
                'empty_data' => 25,
                'choices' => $amountChoices,
                'input_group' => ['right' => 'pixels']
            ])
            ->add('rowedit', ChoiceType::class, [
                'label' => 'Minimum row height for rule editing view',
                'empty_data' => 35,
                'choices' => $amountChoices,
                'input_group' => ['right' => 'pixels']
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
        return 'zikulapermissionsmodule_config';
    }
}

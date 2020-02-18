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

namespace Zikula\ExtensionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemsperpage', IntegerType::class, [
                'label' => 'Items per page',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0)
                ]
            ])
            ->add('helpUiMode', ChoiceType::class, [
                'label' => 'Help UI Mode',
                'choices' => [
                    'Modal window' => 'modal',
                    'Sidebar on right side' => 'sidebar-right',
                    'Sidebar on left side' => 'sidebar-left'
                ],
                'help' => 'How help documents for an extension should be displayed.'
            ])
            ->add('hardreset', CheckboxType::class, [
                'label' => 'Reset all extensions to default values',
                'label_attr' => ['class' => 'switch-custom'],
                'mapped' => false,
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-secondary'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaextensionsmodule_config';
    }
}

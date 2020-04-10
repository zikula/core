<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Zikula\BlocksModule\Api\BlockApi;

class AdminViewFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sort-field', HiddenType::class)
            ->add('sort-direction', HiddenType::class)
            ->add('position', ChoiceType::class, [
                'label' => 'Position',
                'choices' => /** @Ignore */$options['positionChoices'],
                'required' => false,
                'placeholder' => 'All',
                'attr' => [
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('module', ChoiceType::class, [
                'label' => 'Module',
                'choices' => /** @Ignore */$options['moduleChoices'],
                'required' => false,
                'placeholder' => 'All',
                'attr' => [
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Language',
                'choices' => /** @Ignore */$options['localeChoices'],
                'required' => false,
                'placeholder' => 'All',
                'attr' => [
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('active', ChoiceType::class, [
                'label' => 'Active',
                'choices' => [
                    'Active' => BlockApi::BLOCK_ACTIVE,
                    'Inactive' => BlockApi::BLOCK_INACTIVE,
                ],
                'required' => false,
                'placeholder' => 'All',
                'attr' => [
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('filterButton', SubmitType::class, [
                'label' => 'Filter',
                'icon' => 'fa-filter fa-lg',
                'attr' => [
                    'class' => 'btn-sm'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_adminviewfilter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form-inline',
            ],
            'moduleChoices' => [],
            'positionChoices' => [],
            'localeChoices' => [
                'English' => 'en'
            ]
        ]);
    }
}

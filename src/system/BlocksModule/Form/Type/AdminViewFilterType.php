<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\Common\Translator\IdentityTranslator;

class AdminViewFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sort-field', HiddenType::class)
            ->add('sort-direction', HiddenType::class)
            ->add('position', ChoiceType::class, [
                'choices' => $options['positionChoices'],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('module', ChoiceType::class, [
                'choices' => $options['moduleChoices'],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('language', ChoiceType::class, [
                'choices' => $options['localeChoices'],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('active', ChoiceType::class, [
                'choices' => [
                    $options['translator']->__('Active') => BlockApi::BLOCK_ACTIVE,
                    $options['translator']->__('Inactive') => BlockApi::BLOCK_INACTIVE,
                ],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('filterButton', SubmitType::class, [
                'label' => $options['translator']->__('Filter'),
                'icon' => 'fa-filter fa-lg',
                'attr' => [
                    'class' => 'btn btn-default btn-sm'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_adminviewfilter';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form form-inline',
            ],
            'translator' => new IdentityTranslator(),
            'moduleChoices' => [],
            'positionChoices' => [],
            'localeChoices' => ['English' => 'en']
        ]);
    }
}

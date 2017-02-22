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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\Common\Translator\IdentityTranslator;

class AdminViewFilterType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sort-field', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('sort-direction', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('position', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $options['positionChoices'],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('module', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $options['moduleChoices'],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('language', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $options['localeChoices'],
                'choices_as_values' => true,
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('active', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => [
                    $options['translator']->__('Active') => BlockApi::BLOCK_ACTIVE,
                    $options['translator']->__('Inactive') => BlockApi::BLOCK_INACTIVE,
                ],
                'choices_as_values' => true,
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('filterButton', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Filter'),
                'icon' => 'fa-filter fa-lg',
                'attr' => [
                    'class' => 'btn btn-default btn-sm'
                ]
            ])
        ;
    }

    /**
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_adminviewfilter';
    }

    /**
* @inheritDoc
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

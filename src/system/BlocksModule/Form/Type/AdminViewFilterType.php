<?php
/**
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

class AdminViewFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sort-field', 'hidden')
            ->add('sort-direction', 'hidden')
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
                'choices' => \ZLanguage::getInstalledLanguageNames(),
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('active', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => [
                    BlockApi::BLOCK_ACTIVE => $options['translator']->__('Active'),
                    BlockApi::BLOCK_INACTIVE => $options['translator']->__('Inactive'),
                ],
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

    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_adminviewfilter';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form form-inline',
            ],
            'translator' => null,
            'moduleChoices' => [],
            'positionChoices' => []
        ]);
    }
}

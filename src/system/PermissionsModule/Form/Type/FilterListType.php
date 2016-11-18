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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('filterGroup', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => array_flip($options['groupChoices']),
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('filterComponent', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $options['componentChoices'],
                'attr' => [
                    'class' => 'input-sm'
                ]
            ])
            ->add('reset', 'Symfony\Component\Form\Extension\Core\Type\ButtonType', [
                'label' => $translator->__('Reset'),
                'icon' => 'fa-times',
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
        return 'zikulapermissionsmodule_filterlist';
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
            'translator' => null,
            'groupChoices' => [],
            'componentChoices' => []
        ]);
    }
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * IDS Log export form type class.
 */
class IdsLogExportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('titles', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Export Title Row'),
                'empty_data' => 1,
                'required' => false
            ])
            ->add('file', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('CSV filename'),
                'required' => false
            ])
            ->add('delimiter', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('CSV delimiter'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Comma') . ' (,)' => 1,
                    $translator->__('Semicolon') . ' (;)' => 2,
                    $translator->__('Colon') . ' (:)' => 3,
                    $translator->__('Tab') => 4
                ],
                'choices_as_values' => true,
                'multiple' => false,
                'expanded' => false
            ])
            ->add('export', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Export'),
                'icon' => 'fa-download',
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
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulasecuritycentermodule_idslogexport';
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

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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('titles', CheckboxType::class, [
                'label' => $translator->__('Export Title Row'),
                'empty_data' => 1,
                'required' => false
            ])
            ->add('file', TextType::class, [
                'label' => $translator->__('CSV filename'),
                'required' => false
            ])
            ->add('delimiter', ChoiceType::class, [
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
            ->add('export', SubmitType::class, [
                'label' => $translator->__('Export'),
                'icon' => 'fa-download',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
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

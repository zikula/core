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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titles', CheckboxType::class, [
                'label' => 'Export title row',
                'label_attr' => ['class' => 'switch-custom'],
                'empty_data' => 1,
                'required' => false
            ])
            ->add('file', TextType::class, [
                'label' => 'CSV filename',
                'required' => false
            ])
            ->add('delimiter', ChoiceType::class, [
                'label' => 'CSV delimiter',
                'empty_data' => 1,
                'choices' => [
                    'Comma (,)' => 1,
                    'Semicolon (;)' => 2,
                    'Colon (:)' => 3,
                    'Tab' => 4
                ],
                'multiple' => false,
                'expanded' => false
            ])
            ->add('export', SubmitType::class, [
                'label' => 'Export',
                'icon' => 'fa-download',
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
        return 'zikulasecuritycentermodule_idslogexport';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'security'
        ]);
    }
}

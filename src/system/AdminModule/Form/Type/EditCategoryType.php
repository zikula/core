<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Category editing form type class.
 */
class EditCategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('cid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [])
            ->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Name'),
                'attr' => [
                    'maxlength' => 50
                ]
            ])
            ->add('description', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Description'),
                'required' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
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
            ->add('help', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Help'),
                'icon' => 'fa-question',
                'attr' => [
                    'class' => 'btn btn-info'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulaadminmodule_editcategory';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\AdminModule\Entity\AdminCategoryEntity',
            'translator' => null
        ]);
    }
}

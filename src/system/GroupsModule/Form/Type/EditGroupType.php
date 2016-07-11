<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\GroupsModule\Helper\CommonHelper;

/**
 * Group editing form type class.
 */
class EditGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $groupsCommon = new CommonHelper();
        $typeChoices = array_flip($groupsCommon->gtypeLabels());
        $stateChoices = array_flip($groupsCommon->stateLabels());

        $builder
            ->add('gid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [])
            ->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Name'),
                'empty_data' => '',
                'max_length' => 30
            ])
            ->add('gtype', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Type'),
                'empty_data' => 0,
                'choices' => $typeChoices,
                'choices_as_values' => true,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('state', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('State'),
                'empty_data' => 0,
                'choices' => $stateChoices,
                'choices_as_values' => true,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('nbumax', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Maximum membership'),
                'empty_data' => 0,
                'max_length' => 10,
                'attr' => [
                    'min' => 0
                ],
                'required' => false
            ])
            ->add('description', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Description'),
                'empty_data' => '',
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
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulagroupsmodule_editgroup';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
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

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
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\GroupsModule\Validator\Constraints\ValidGroupName;

/**
 * Group editing form type class.
 */
class EditGroupType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $groupsCommon = new CommonHelper($translator);
        $typeChoices = array_flip($groupsCommon->gtypeLabels());
        $stateChoices = array_flip($groupsCommon->stateLabels());

        $builder
            ->add('gid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Name'),
                'attr' => [
                    'maxlength' => 30
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('gtype', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Type'),
                'choices' => $typeChoices,
                'choices_as_values' => true,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('state', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('State'),
                'choices' => $stateChoices,
                'choices_as_values' => true,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('nbumax', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Maximum membership'),
                'attr' => [
                    'maxlength' => 10,
                    'min' => 0
                ],
                'required' => false
            ])
            ->add('description', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Description'),
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
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulagroupsmodule_editgroup';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\GroupsModule\Entity\GroupEntity',
            'translator' => null,
            'constraints' => [
                new ValidGroupName()
            ]
        ]);
    }
}

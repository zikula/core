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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\GroupsModule\Validator\Constraints\ValidGroupName;

/**
 * Group creation form type class.
 */
class CreateGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $groupsCommon = new CommonHelper($translator);
        $typeChoices = array_flip($groupsCommon->gtypeLabels());
        $stateChoices = array_flip($groupsCommon->stateLabels());

        $builder
            ->add('name', TextType::class, [
                'label' => $translator->__('Name'),
                'attr' => ['max_length' => 30],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('gtype', ChoiceType::class, [
                'label' => $translator->__('Type'),
                'choices' => $typeChoices,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('state', ChoiceType::class, [
                'label' => $translator->__('State'),
                'choices' => $stateChoices,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('nbumax', IntegerType::class, [
                'label' => $translator->__('Maximum membership'),
                'attr' => ['max_length' => 10],
                'required' => false,
                'help' => $translator->__('Set as 0 for unlimited.')
            ])
            ->add('description', TextareaType::class, [
                'label' => $translator->__('Description'),
            ])
            ->add('save', SubmitType::class, [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
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
        return 'zikulagroupsmodule_creategroup';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\GroupsModule\Entity\GroupEntity',
            'translator' => null,
            'constraints' => new ValidGroupName()
        ]);
    }
}

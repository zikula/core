<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Translation\Extractor\Annotation\Ignore;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\GroupsModule\Validator\Constraints\ValidGroupName;

/**
 * Group editing form type class.
 */
class EditGroupType extends AbstractType
{
    /**
     * @var CommonHelper
     */
    private $groupsCommon;

    public function __construct(CommonHelper $commonHelper)
    {
        $this->groupsCommon = $commonHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeChoices = array_flip($this->groupsCommon->gtypeLabels());
        $stateChoices = array_flip($this->groupsCommon->stateLabels());

        $builder
            ->add('gid', HiddenType::class)
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'maxlength' => 30
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('gtype', ChoiceType::class, [
                'label' => 'Type',
                'choices' => /** @Ignore */ $typeChoices,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('state', ChoiceType::class, [
                'label' => 'State',
                'choices' => /** @Ignore */ $stateChoices,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('nbumax', IntegerType::class, [
                'label' => 'Maximum membership',
                'attr' => [
                    'maxlength' => 10,
                    'min' => 0
                ],
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
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
        return 'zikulagroupsmodule_editgroup';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupEntity::class,
            'constraints' => [
                new ValidGroupName()
            ]
        ]);
    }
}

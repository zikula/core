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

namespace Zikula\GroupsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\GroupsModule\Validator\Constraints\ValidGroupName;

/**
 * Group creation form type class.
 */
class CreateGroupType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $groupsCommon = new CommonHelper($this->translator);
        $typeChoices = array_flip($groupsCommon->gtypeLabels());
        $stateChoices = array_flip($groupsCommon->stateLabels());

        $builder
            ->add('name', TextType::class, [
                'label' => $this->__('Name'),
                'attr' => [
                    'maxlength' => 30
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('gtype', ChoiceType::class, [
                'label' => $this->__('Type'),
                'choices' => $typeChoices,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('state', ChoiceType::class, [
                'label' => $this->__('State'),
                'choices' => $stateChoices,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('nbumax', IntegerType::class, [
                'label' => $this->__('Maximum membership'),
                'attr' => [
                    'maxlength' => 10
                ],
                'required' => false,
                'help' => $this->__('Set as 0 for unlimited.')
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->__('Description'),
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
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
            'data_class' => GroupEntity::class,
            'constraints' => new ValidGroupName()
        ]);
    }
}

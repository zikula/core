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

namespace Zikula\PermissionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $amountChoices = [
            20 => 20,
            25 => 25,
            30 => 30,
            35 => 35,
            40 => 40
        ];

        $builder
            ->add('lockadmin', CheckboxType::class, [
                'label' => $this->__('Lock main administration permission rule'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('adminid', IntegerType::class, [
                'label' => $this->__('ID of main administration permission rule'),
                'empty_data' => 1,
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('filter', CheckboxType::class, [
                'label' => $this->__('Enable filtering of group permissions'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('rowview', ChoiceType::class, [
                'label' => $this->__('Minimum row height for permission rules list view (in pixels)'),
                'empty_data' => 25,
                'choices' => $amountChoices
            ])
            ->add('rowedit', ChoiceType::class, [
                'label' => $this->__('Minimum row height for rule editing view (in pixels)'),
                'empty_data' => 35,
                'choices' => $amountChoices
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

    public function getBlockPrefix()
    {
        return 'zikulapermissionsmodule_config';
    }
}

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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * IDS Log export form type class.
 */
class IdsLogExportType extends AbstractType
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
        $builder
            ->add('titles', CheckboxType::class, [
                'label' => $this->__('Export Title Row'),
                'label_attr' => ['class' => 'switch-custom'],
                'empty_data' => 1,
                'required' => false
            ])
            ->add('file', TextType::class, [
                'label' => $this->__('CSV filename'),
                'required' => false
            ])
            ->add('delimiter', ChoiceType::class, [
                'label' => $this->__('CSV delimiter'),
                'empty_data' => 1,
                'choices' => [
                    $this->__('Comma') . ' (,)' => 1,
                    $this->__('Semicolon') . ' (;)' => 2,
                    $this->__('Colon') . ' (:)' => 3,
                    $this->__('Tab') => 4
                ],
                'multiple' => false,
                'expanded' => false
            ])
            ->add('export', SubmitType::class, [
                'label' => $this->__('Export'),
                'icon' => 'fa-download',
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
        return 'zikulasecuritycentermodule_idslogexport';
    }
}

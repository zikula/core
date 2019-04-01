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

namespace Zikula\SearchModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
        $builder
            ->add('itemsperpage', IntegerType::class, [
                'label' => $this->__('Items per page'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 3,
                    'min' => 1
                ]
            ])
            ->add('limitsummary', IntegerType::class, [
                'label' => $this->__('Number of characters to display in item summaries'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 5
                ]
            ])
            ->add('plugins', ChoiceType::class, [
                'label' => $this->__('Disabled plugins'),
                'label_attr' => ['class' => 'checkbox-inline'],
                'choices' => $options['plugins'],
                'expanded' => true,
                'multiple' => true,
                'required' => false
            ])
            ->add('opensearch_enabled', CheckboxType::class, [
                'label' => $this->__('Enable OpenSearch'),
                'required' => false
            ])
            ->add('opensearch_adult_content', CheckboxType::class, [
                'label' => $this->__('This page contains adult content'),
                'required' => false
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
        return 'zikulasearchmodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'plugins' => []
        ]);
    }
}

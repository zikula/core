<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;

class DateTimeFormOptionsArrayType extends FormOptionsArrayType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('html5', CheckboxType::class, [
                'label' => $this->translator->__('Html5'),
                'required' => false,
            ])
            ->add('widget', ChoiceType::class, [
                'label' => $this->translator->__('Widget'),
                'choices' => [
                    $this->translator->__('Choice') => 'choice',
                    $this->translator->__('Text') => 'text',
                    $this->translator->__('Single Text') => 'single_text',
                ],
                'choices_as_values' => true,
            ])
            ->add('input', ChoiceType::class, [
                'label' => $this->translator->__('Input'),
                'choices' => [
                    $this->translator->__('String') => 'string',
                    $this->translator->__('DateTime Object') => 'datetime',
                    $this->translator->__('Array') => 'array',
                    $this->translator->__('Timestamp') => 'timestamp',
                ],
                'choices_as_values' => true,
            ])
            ->add('format', TextType::class, [
                'label' => $this->translator->__('Format'),
                'help' => $this->translator->__('e.g. yyyy-MM-dd'),
                'required' => false,
            ])
            ->add('model_timezone', TimezoneType::class)
        ;
    }
}

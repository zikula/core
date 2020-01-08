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

namespace Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;

class DateTimeFormOptionsArrayType extends FormOptionsArrayType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('html5', CheckboxType::class, [
                'label' => $this->trans('Html5'),
                'required' => false
            ])
            ->add('widget', ChoiceType::class, [
                'label' => $this->trans('Widget'),
                'choices' => [
                    $this->trans('Choice') => 'choice',
                    $this->trans('Text') => 'text',
                    $this->trans('Single Text') => 'single_text'
                ]
            ])
            ->add('input', ChoiceType::class, [
                'label' => $this->trans('Input'),
                'choices' => [
                    $this->trans('String') => 'string',
                    $this->trans('DateTime Object') => 'datetime',
                    $this->trans('Array') => 'array',
                    $this->trans('Timestamp') => 'timestamp'
                ]
            ])
            ->add('format', TextType::class, [
                'label' => $this->trans('Format'),
                'help' => $this->trans('e.g. yyyy-MM-dd'),
                'required' => false
            ])
            ->add('model_timezone', TimezoneType::class)
        ;
    }
}

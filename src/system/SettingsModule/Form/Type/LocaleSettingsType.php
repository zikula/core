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

namespace Zikula\SettingsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Locale settings form type.
 */
class LocaleSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('multilingual', CheckboxType::class, [
                'label' => 'Activate multilingual features',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('languageurl', ChoiceType::class, [
                'label' => 'Prepend language to URL',
                'label_attr' => ['class' => 'radio-custom'],
                'expanded' => true,
                'choices' => [
                    'Always' => 1,
                    'Only for non-default languages' => 0
                ]
            ])
            ->add('language_detect', CheckboxType::class, [
                'label' => 'Automatically detect language from browser settings',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'help' => 'If this is checked, Zikula tries to serve the language requested by browser (if that language available and allowed by the multi-lingual settings). If users set their personal language preference, then this setting will be overriden by their personal preference.'
            ])
            ->add('language_i18n', ChoiceType::class, [
                'label' => 'Default language to use for this site',
                'choices' => $options['languages']
            ])
            ->add('timezone', TimezoneType::class, [
                'label' => 'Time zone for anonymous guests',
                'help' => 'Server time zone is %tz%',
                'help_translation_parameters' => [
                    '%tz%' => date_default_timezone_get() . ' (' . date('T') . ')'
                ],
                'choice_translation_locale' => $options['locale'],
                'intl' => true
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
        return 'zikulasettingsmodule_localesettings';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'languages' => ['English' => 'en'],
            'locale' => 'en',
            'timezones' => [0 => 'GMT']
        ]);
    }
}

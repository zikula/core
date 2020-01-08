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
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Locale settings form type.
 */
class LocaleSettingsType extends AbstractType
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
            ->add('multilingual', CheckboxType::class, [
                'label' => $this->trans('Activate multilingual features'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('languageurl', ChoiceType::class, [
                'label' => $this->trans('Prepend language to URL'),
                'label_attr' => ['class' => 'radio-custom'],
                'expanded' => true,
                'choices' => [
                    $this->trans('Always') => 1,
                    $this->trans('Only for non-default languages') => 0
                ]
            ])
            ->add('language_detect', CheckboxType::class, [
                'label' => $this->trans('Automatically detect language from browser settings'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'help' => $this->trans('If this is checked, Zikula tries to serve the language requested by browser (if that language available and allowed by the multi-lingual settings). If users set their personal language preference, then this setting will be overriden by their personal preference.')
            ])
            ->add('language_i18n', ChoiceType::class, [
                'label' => $this->trans('Default language to use for this site'),
                'choices' => $options['languages']
            ])
            ->add('timezone', TimezoneType::class, [
                'label' => $this->trans('Time zone for anonymous guests'),
                'help' => $this->trans('Server time zone is %tz', ['%tz' => date_default_timezone_get() . ' (' . date('T') . ')']),
                'choice_translation_locale' => $options['locale'],
                'intl' => true
            ])
            ->add('idnnames', CheckboxType::class, [
                'label' => $this->trans('Allow IDN domain names'),
                'label_attr' => ['class' => 'switch-custom'],
                'help' => [
                    $this->trans('This only applies to legacy variable validation. The system itself has native IDN support.'),
                    $this->trans('Notice: With IDN domains, special characters are allowed in e-mail addresses and URLs.')
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->trans('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->trans('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
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

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
use Zikula\Common\Translator\IdentityTranslator;

/**
 * Locale settings form type.
 */
class LocaleSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('multilingual', CheckboxType::class, [
                'label' => $translator->__('Activate multilingual features'),
                'required' => false
            ])
            ->add('languageurl', ChoiceType::class, [
                'label' => $translator->__('Prepend language to URL'),
                'expanded' => true,
                'choices' => [
                    $translator->__('Always') => 1,
                    $translator->__('Only for non-default languages') => 0,
                ],
            ])
            ->add('language_detect', CheckboxType::class, [
                'label' => $translator->__('Automatically detect language from browser settings'),
                'required' => false,
                'help' => $translator->__('If this is checked, Zikula tries to serve the language requested by browser (if that language available and allowed by the multi-lingual settings). If users set their personal language preference, then this setting will be overriden by their personal preference.')
            ])
            ->add('language_i18n', ChoiceType::class, [
                'label' => $translator->__('Default language to use for this site'),
                'choices' => $options['languages'],
            ])
            ->add('timezone', TimezoneType::class, [
                'label' => $translator->__('Time zone for anonymous guests'),
                'help' => $translator->__f('Server time zone is %tz', ['%tz' => date_default_timezone_get() . ' (' . date('T') . ')'])
            ])
            ->add('idnnames', CheckboxType::class, [
                'label' => $translator->__('Allow IDN domain names'),
                'help' => [
                    $translator->__('This only applies to legacy variable validation. The system itself has native IDN support.'),
                    $translator->__('Notice: With IDN domains, special characters are allowed in e-mail addresses and URLs.')
                ]
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
        return 'zikulasettingsmodule_localesettings';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => new IdentityTranslator(),
            'languages' => ['English' => 'en'],
            'timezones' => [0 => 'GMT']
        ]);
    }
}

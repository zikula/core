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

use DateUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('multilingual', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Activate multilingual features')
            ])
            ->add('languageurl', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Prepend language to URL'),
                'expanded' => true,
                'choices' => [
                    1 => $translator->__('Always'),
                    0 => $translator->__('Only for non-default languages'),
                ]
            ])
            ->add('language_detect', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Automatically detect language from browser settings'),
                'help' => $translator->__('If this is checked, Zikula tries to serve the language requested by browser (if that language available and allowed by the multi-lingual settings). If users set their personal language preference, then this setting will be overriden by their personal preference.')
            ])
            ->add('language_i18n', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Default language to use for this site'),
                'choices' => $options['languages']
            ])
            ->add('timezone_offset', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $options['timezones'],
                'label' => $translator->__('Time zone for anonymous guests'),
                'help' => $translator->__('Server time zone') . ': ' . DateUtil::getTimezoneAbbr()
            ])
            ->add('idnnames', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Allow IDN domain names'),
                'help' => [
                    $translator->__('This only applies to legacy variable validation. The system itself has native IDN support.'),
                    $translator->__('Notice: With IDN domains, special characters are allowed in e-mail addresses and URLs.')
                ]
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
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
            'translator' => null,
            'languages' => [],
            'timezones' => []
        ]);
    }
}

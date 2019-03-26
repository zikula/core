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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Locale settings form type.
 */
class LocaleSettingsType extends AbstractType
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
        $builder
            ->add('multilingual', CheckboxType::class, [
                'label' => $this->__('Activate multilingual features'),
                'required' => false
            ])
            ->add('languageurl', ChoiceType::class, [
                'label' => $this->__('Prepend language to URL'),
                'expanded' => true,
                'choices' => [
                    $this->__('Always') => 1,
                    $this->__('Only for non-default languages') => 0,
                ],
            ])
            ->add('language_detect', CheckboxType::class, [
                'label' => $this->__('Automatically detect language from browser settings'),
                'required' => false,
                'help' => $this->__('If this is checked, Zikula tries to serve the language requested by browser (if that language available and allowed by the multi-lingual settings). If users set their personal language preference, then this setting will be overriden by their personal preference.')
            ])
            ->add('language_i18n', ChoiceType::class, [
                'label' => $this->__('Default language to use for this site'),
                'choices' => $options['languages'],
            ])
            ->add('timezone', TimezoneType::class, [
                'label' => $this->__('Time zone for anonymous guests'),
                'help' => $this->__f('Server time zone is %tz', ['%tz' => date_default_timezone_get() . ' (' . date('T') . ')'])
            ])
            ->add('idnnames', CheckboxType::class, [
                'label' => $this->__('Allow IDN domain names'),
                'help' => [
                    $this->__('This only applies to legacy variable validation. The system itself has native IDN support.'),
                    $this->__('Notice: With IDN domains, special characters are allowed in e-mail addresses and URLs.')
                ]
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
        return 'zikulasettingsmodule_localesettings';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'languages' => ['English' => 'en'],
            'timezones' => [0 => 'GMT']
        ]);
    }
}

<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SettingsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('multilingual', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Activate multilingual features')
            ])
            ->add('languageurl', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('Prepend language to URL'),
                'expanded' => true,
                'choices' => [
                    1 => $options['translator']->__('Always'),
                    0 => $options['translator']->__('Only for non-default languages'),
                ]
            ])
            ->add('language_detect', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Automatically detect language from browser settings')
            ])
            ->add('language_i18n', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('Default language to use for this site'),
                'choices' => $options['languages']
            ])
            ->add('timezone_offset', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $options['timezones'],
                'label' => $options['translator']->__('Time zone for anonymous guests'),
            ])
            ->add('idnnames', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Allow IDN domain names')
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Save')
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel')
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulasettingsmodule_localesettings';
    }

    /**
     * @param OptionsResolver $resolver
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

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('ignoreinstallercheck', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Ignore check for installer'),
                'required' => false
            ])
            ->add('admingraphic', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Display icons'),
                'required' => false
            ])
            ->add('displaynametype', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Form of display for module names'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Display name') => 1,
                    $translator->__('Internal name') => 2,
                    $translator->__('Show both internal name and display name') => 3
                ]
            ])
            ->add('itemsperpage', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Modules per page in module categories list'),
                'empty_data' => 5,
                'scale' => 0,
                'max_length' => 3
            ])
            ->add('modulesperrow', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Modules per row in admin panel'),
                'empty_data' => 5,
                'scale' => 0,
                'max_length' => 3
            ])
            ->add('admintheme', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Theme to use'),
                'required' => false,
                'empty_data' => null,
                'choices' => $this->formatThemeSelector($options['themes']),
                'placeholder' => $translator->__('Use site\'s theme')
            ])
            ->add('startcategory', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Initially selected category'),
                'empty_data' => null,
                'choices' => $options['categories']
            ])
            ->add('defaultcategory', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Default category for newly-added modules'),
                'empty_data' => null,
                'choices' => $options['categories']
            ])
        ;

        foreach ($options['modules'] as $module) {
            $builder->add('modulecategory' . $module['name'], 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $module['displayname'],
                'empty_data' => null,
                'choices' => $options['categories']
            ]);
        }

        $builder
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
            ->add('help', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Help'),
                'icon' => 'fa-question',
                'attr' => [
                    'class' => 'btn btn-info'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulaadminmodule_config';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'categories' => [],
            'modules' => [],
            'themes' => []
        ]);
    }

    /**
     * Returns a list of choices for the admin theme selection.
     *
     * @param array $themes
     *
     * @return array Choices list
     */
    private function formatThemeSelector(array $themes)
    {
        $choices = [];
        $themeList = [];

        if (!empty($themes)) {
            foreach ($themes as $name => $theme) {
                $themeList[$name] = $theme['displayname'];
            }
            natcasesort($themeList);
            foreach ($themeList as $k => $v) {
                $choices[$v] = $k;
            }
        }

        return $choices;
    }
}

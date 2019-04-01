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

namespace Zikula\AdminModule\Form\Type;

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
            ->add('ignoreinstallercheck', CheckboxType::class, [
                'label' => $this->__('Ignore check for installer'),
                'required' => false
            ])
            ->add('admingraphic', CheckboxType::class, [
                'label' => $this->__('Display icons'),
                'required' => false
            ])
            ->add('displaynametype', ChoiceType::class, [
                'label' => $this->__('Form of display for module names'),
                'empty_data' => 1,
                'choices' => [
                    $this->__('Display name') => 1,
                    $this->__('Internal name') => 2,
                    $this->__('Show both internal name and display name') => 3
                ]
            ])
            ->add('itemsperpage', IntegerType::class, [
                'label' => $this->__('Modules per page in module categories list'),
                'empty_data' => 5,
                'scale' => 0,
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('modulesperrow', IntegerType::class, [
                'label' => $this->__('Modules per row in admin panel'),
                'empty_data' => 5,
                'scale' => 0,
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('admintheme', ChoiceType::class, [
                'label' => $this->__('Theme to use'),
                'required' => false,
                'empty_data' => null,
                'choices' => $this->formatThemeSelector($options['themes']),
                'placeholder' => $this->__('Use site\'s theme')
            ])
            ->add('startcategory', ChoiceType::class, [
                'label' => $this->__('Initially selected category'),
                'empty_data' => null,
                'choices' => $options['categories']
            ])
            ->add('defaultcategory', ChoiceType::class, [
                'label' => $this->__('Default category for newly-added modules'),
                'empty_data' => null,
                'choices' => $options['categories']
            ])
        ;

        foreach ($options['modules'] as $module) {
            $builder->add('modulecategory' . $module['name'], ChoiceType::class, [
                'label' => $module['displayname'],
                'empty_data' => null,
                'choices' => $options['categories']
            ]);
        }

        $builder
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
            ->add('help', SubmitType::class, [
                'label' => $this->__('Help'),
                'icon' => 'fa-question',
                'attr' => [
                    'class' => 'btn btn-info'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaadminmodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'categories' => [],
            'modules' => [],
            'themes' => []
        ]);
    }

    /**
     * Returns a list of choices for the admin theme selection.
     */
    private function formatThemeSelector(array $themes = []): array
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

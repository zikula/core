<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('defaulttheme', ChoiceType::class, [
                'label' => 'Theme to use for main site',
                'required' => true,
                'empty_data' => null,
                'choices' => /** @Ignore */$this->formatThemeSelector($options['themes']),
            ])
            ->add('admintheme', ChoiceType::class, [
                'label' => 'Theme to use for admin controllers',
                'required' => false,
                'empty_data' => null,
                'choices' => /** @Ignore */$this->formatThemeSelector($options['themes']),
                'placeholder' => 'Use site\'s theme'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulathememodule_theme';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'themes' => []
        ]);
    }

    /**
     * Returns a list of choices for the admin theme selection.
     */
    private function formatThemeSelector(array $themes = []): array
    {
        $choices = [];
        if (!empty($themes)) {
            foreach ($themes as $theme) {
                $choices[$theme['displayname']] = $theme['name'];
            }
            natcasesort($choices);
        }

        return $choices;
    }
}

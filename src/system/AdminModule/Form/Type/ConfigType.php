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

namespace Zikula\AdminModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ignoreinstallercheck', CheckboxType::class, [
                'label' => 'Ignore check for installer',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('admingraphic', CheckboxType::class, [
                'label' => 'Display icons',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('displaynametype', ChoiceType::class, [
                'label' => 'Form of display for module names',
                'empty_data' => 1,
                'choices' => [
                    'Display name' => 1,
                    'Internal name' => 2,
                    'Show both internal name and display name' => 3
                ]
            ])
            ->add('itemsperpage', IntegerType::class, [
                'label' => 'Modules per page in module categories list',
                'empty_data' => 5,
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('modulesperrow', IntegerType::class, [
                'label' => 'Modules per row in admin panel',
                'empty_data' => 5,
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('startcategory', ChoiceType::class, [
                'label' => 'Initially selected category',
                'empty_data' => null,
                'choices' => /** @Ignore */ $options['categories']
            ])
            ->add('defaultcategory', ChoiceType::class, [
                'label' => 'Default category for newly-added modules',
                'empty_data' => null,
                'choices' => /** @Ignore */ $options['categories']
            ])
        ;

        foreach ($options['modules'] as $module) {
            $builder->add('modulecategory' . $module['name'], ChoiceType::class, [
                /** @Ignore */
                'label' => $module['displayname'],
                'empty_data' => null,
                'choices' => /** @Ignore */ $options['categories']
            ]);
        }

        $builder
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
        return 'zikulaadminmodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'categories' => [],
            'modules' => [],
        ]);
    }
}

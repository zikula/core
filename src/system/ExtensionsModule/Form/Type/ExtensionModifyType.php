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

namespace Zikula\ExtensionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Zikula\Bundle\FormExtensionBundle\Form\Type\IconType;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

/**
 * Extension modification form type.
 */
class ExtensionModifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('displayname', TextType::class, [
                'label' => 'Display name'
            ])
            ->add('url', TextType::class, [
                'label' => 'URL',
                'input_group' => ['left' => '/'],
                'help' => 'WARNING: changing the url affects SEO by breaking existing indexed search results.'
            ])
            ->add($builder->create('description', TextType::class, [
                'label' => 'Description',
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add($builder->create('icon', IconType::class, [
                'label' => 'Icon',
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
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
            ->add('defaults', SubmitType::class, [
                'label' => 'Reload defaults',
                'icon' => 'fa-sync'
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaextensionsmodule_extensionmodify';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExtensionEntity::class
        ]);
    }
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Validator\Constraints\UniqueNameForPosition;

/**
 * CategoryType form type class.
 */
class CategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Name'),
                'constraints' => [new NotBlank()]
            ])
            ->add('parent', 'Zikula\CategoriesModule\Form\Type\CategoryTreeType', [
                'label' => $translator->__('Parent'),
                'translator' => $translator,
                'includeRoot' => true,
                'includeLeaf' => false,
                'constraints' => [new NotBlank()]
            ])
            ->add('is_locked', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Category is locked'),
                'required' => false
            ])
            ->add('is_leaf', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Category is a leaf node'),
                'required' => false
            ])
            ->add($builder->create('value', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Value'),
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add('status', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Active'),
                'required' => false
            ])
            ->add('display_name', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'entry_type' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                'label' => $translator->__('Display name'),
                'required' => false
            ])
            ->add('display_desc', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'entry_type' => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                'label' => $translator->__('Display description'),
                'required' => false
            ])
            ->add('attributes', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'entry_type' => 'Zikula\CategoriesModule\Form\Type\CategoryAttributeType',
                'entry_options' => ['translator' => $translator],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => $translator->__('Category attributes'),
                'required' => false
            ])
            ->add('after', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'mapped' => false,
                'required' => false
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options) {
                // ensure all locales have a display name
                /** @var CategoryEntity $category */
                $category = $event->getData();
                $name = $category->getName();
                $displayName = $category->getDisplay_name();
                foreach ($options['locales'] as $code) {
                    if (!isset($displayName[$code]) || !$displayName[$code]) {
                        $displayName[$code] = $options['translator']->__(/** @Ignore */$name, 'zikula', $code);
                    }
                }
                $category->setDisplay_name($displayName);
                $event->setData($category);
            })
        ;
        $builder->get('name')
            ->addModelTransformer(new CallbackTransformer(
                // remove slash from name before persistence to prevent issues with path
                function ($string) {
                    return $string;
                },
                function ($string) {
                    return str_replace('/', '&#47;', $string);
                }
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulacategoriesmodule_category';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\CategoriesModule\Entity\CategoryEntity',
            'translator' => null,
            'locales' => [],
            'constraints' => [new UniqueNameForPosition()]
        ]);
    }
}

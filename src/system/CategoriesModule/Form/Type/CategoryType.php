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

namespace Zikula\CategoriesModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Validator\Constraints\UniqueNameForPosition;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * CategoryType form type class.
 */
class CategoryType extends AbstractType
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
        $translator = $this->translator;
        $builder
            ->add('name', TextType::class, [
                'label' => $this->__('Name'),
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('parent', CategoryTreeType::class, [
                'label' => $this->__('Parent'),
                'includeRoot' => true,
                'includeLeaf' => false,
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('is_locked', CheckboxType::class, [
                'label' => $this->__('Category is locked'),
                'required' => false
            ])
            ->add('is_leaf', CheckboxType::class, [
                'label' => $this->__('Category is a leaf node'),
                'required' => false
            ])
            ->add($builder->create('value', TextType::class, [
                'label' => $this->__('Value'),
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add('status', CheckboxType::class, [
                'label' => $this->__('Active'),
                'required' => false
            ])
            ->add('display_name', CollectionType::class, [
                'entry_type' => TextType::class,
                'label' => $this->__('Display name'),
                'required' => false
            ])
            ->add('display_desc', CollectionType::class, [
                'entry_type' => TextareaType::class,
                'label' => $this->__('Display description'),
                'required' => false
            ])
            ->add('attributes', CollectionType::class, [
                'entry_type' => CategoryAttributeType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => $this->__('Category attributes'),
                'required' => false
            ])
            ->add('after', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($translator, $options) {
                // ensure all display name and description exist for all locales
                /** @var CategoryEntity $category */
                $category = $event->getData();

                $name = $category->getName();

                $displayName = $category->getDisplay_name();
                $displayDesc = $category->getDisplay_desc();

                foreach ($options['locales'] as $code) {
                    if (!isset($displayName[$code]) || !$displayName[$code]) {
                        $displayName[$code] = $translator->__(/** @Ignore */$name, 'zikula', $code);
                    }
                    if (!isset($displayDesc[$code])) {
                        $displayDesc[$code] = '';
                    }
                }

                $category->setDisplay_name($displayName);
                $category->setDisplay_desc($displayDesc);

                $event->setData($category);
            })
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($translator, $options) {
                // ensure all locales have a display name
                /** @var CategoryEntity $category */
                $category = $event->getData();

                $name = $category->getName();
                $displayName = $category->getDisplay_name();

                foreach ($options['locales'] as $code) {
                    if (!isset($displayName[$code]) || !$displayName[$code]) {
                        $displayName[$code] = $translator->__(/** @Ignore */$name, 'zikula', $code);
                    }
                }
                $category->setDisplay_name($displayName);

                $event->setData($category);
            })
        ;
        $builder->get('name')
            ->addModelTransformer(new CallbackTransformer(
                // remove slash from name before persistence to prevent issues with path
                function($string) {
                    return $string;
                },
                function($string) {
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
            'data_class' => CategoryEntity::class,
            'locales' => [],
            'constraints' => [
                new UniqueNameForPosition()
            ]
        ]);
    }
}

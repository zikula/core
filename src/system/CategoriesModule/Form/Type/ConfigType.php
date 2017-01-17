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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\CategoriesModule\Api\CategoryApi;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    /**
     * @var CategoryApi
     */
    private $categoryApi;

    /**
     * ConfigType constructor.
     *
     * @param CategoryApi $categoryApi CategoryApi service instance
     */
    public function __construct(CategoryApi $categoryApi)
    {
        $this->categoryApi = $categoryApi;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('userrootcat', CategoryTreeType::class, [
                'label' => $translator->__('Root category for user categories'),
                'empty_data' => '/__SYSTEM__/Users',
                'translator' => $translator,
                'valueField' => 'path'
            ])
            ->add('allowusercatedit', CheckboxType::class, [
                'label' => $translator->__('Allow users to edit their own categories'),
                'required' => false
            ])
            ->add('autocreateusercat', CheckboxType::class, [
                'label' => $translator->__('Automatically create user category root folder'),
                'required' => false
            ])
            ->add('autocreateuserdefaultcat', CheckboxType::class, [
                'label' => $translator->__('Automatically create user default category'),
                'required' => false
            ])
            ->add('permissionsall', CheckboxType::class, [
                'label' => $translator->__('Require access to all categories for one item (relevant when using multiple categories per content item)'),
                'required' => false
            ])
            ->add('userdefaultcatname', TextType::class, [
                'label' => $translator->__('Default user category'),
                'empty_data' => $translator->__('Default'),
                'attr' => [
                    'maxlength' => 255
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
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
        return 'zikulacategoriesmodule_config';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'locale' => 'en'
        ]);
    }
}

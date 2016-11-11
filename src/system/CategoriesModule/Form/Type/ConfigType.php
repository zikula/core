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

use CategoryUtil;
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
            ->add('userrootcat', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Root category for user categories'),
                'empty_data' => '/__SYSTEM__/Users',
                'choices' => $this->getCategoryChoices($options['locale']),
                'choices_as_values' => true,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => $translator->__('Choose one')
            ])
            ->add('allowusercatedit', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Allow users to edit their own categories'),
                'required' => false
            ])
            ->add('autocreateusercat', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Automatically create user category root folder'),
                'required' => false
            ])
            ->add('autocreateuserdefaultcat', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Automatically create user default category'),
                'required' => false
            ])
            ->add('permissionsall', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Require access to all categories for one item (relevant when using multiple categories per content item)'),
                'required' => false
            ])
            ->add('userdefaultcatname', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Default user category'),
                'empty_data' => $translator->__('Default'),
                'max_length' => 255
            ])
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
    public function getName()
    {
        return $this->getBlockPrefix();
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

    /**
     * Returns choices for category selection.
     *
     * @param string $locale
     * @return array
     */
    private function getCategoryChoices($locale = '')
    {
        $choices = [];

        $recurse = true;
        $relative = true;
        $includeRoot = false;
        $includeLeaf = false;
        $all = false;

        $category = CategoryUtil::getCategoryByID(1);
        $categoryList = CategoryUtil::getSubCategoriesForCategory($category, $recurse, $relative, $includeRoot, $includeLeaf, $all, '', '', null, 'sort_value');

        $line = '---------------------------------------------------------------------';

        foreach ($categoryList as $cat) {
            $amountOfSlashes = mb_substr_count(isset($cat['ipath_relative']) ? $cat['ipath_relative'] : $cat['ipath'], '/');

            $indent = $amountOfSlashes > 0 ? substr($line, 0, $amountOfSlashes * 2) : '';
            $indent = '|' . $indent;

            if (isset($cat['display_name'][$locale]) && !empty($cat['display_name'][$locale])) {
                $catName = $cat['display_name'][$locale];
            } else {
                $catName = $cat['name'];
            }

            $choices[$indent . ' ' . $catName] = $cat['path'];
        }

        return $choices;
    }
}

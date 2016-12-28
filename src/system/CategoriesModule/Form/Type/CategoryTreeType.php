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
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\CategoriesModule\Api\CategoryApi;

/**
 * Category tree form type class.
 */
class CategoryTreeType extends AbstractType
{
    /**
     * @var CategoryApi
     */
    private $categoryApi;

    /**
     * CategoryTreeType constructor.
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
    public function getBlockPrefix()
    {
        return 'zikulacategoriesmodule_category_tree';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'translator'
        ]);
        $resolver->setDefaults([
            'translator' => null,
            'locale' => 'en',
            'choices_as_values' => true
        ]);

        $resolver->setNormalizer('label', function (Options $options, $label) {
            if (null === $label || empty($label)) {
                $isMultiple = $options['multiple'];
                $translator = $options['translator'];

                if (null === $translator) {
                    $label = $isMultiple ? 'Categories' : 'Category';
                } else {
                    $label = $isMultiple ? $translator->__('Categories') : $translator->__('Category');
                }
            }

            return $label;
        });
        $resolver->setNormalizer('placeholder', function (Options $options, $placeholder) {
            if (null == $placeholder || empty($placeholder)) {
                $isMultiple = $options['multiple'];
                $translator = $options['translator'];

                if (null === $translator) {
                    $placeholder = $isMultiple ? 'Choose categories' : 'Choose a category';
                } else {
                    $placeholder = $isMultiple ? $translator->__('Choose categories') : $translator->__('Choose a category');
                }
            }

            return $placeholder;
        });
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            if (empty($choices)) {
                $choices = $this->getCategoryChoices($options['locale']);
            }

            return $choices;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
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

        // TODO expose these flags as form options maybe
        $recurse = true;
        $relative = true;
        $includeRoot = false;
        $includeLeaf = false;
        $all = false;

        $category = $this->categoryApi->getCategoryById(1);
        $categoryList = $this->categoryApi->getSubCategoriesForCategory($category, $recurse, $relative, $includeRoot, $includeLeaf);

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

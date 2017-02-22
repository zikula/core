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
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulacategoriesmodule_category_tree';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'translator'
        ]);
        $resolver->setDefaults([
            'translator' => new \Zikula\Common\Translator\IdentityTranslator(),
            'locale' => 'en',
            'choices_as_values' => true,
            'recurse' => true,
            'relative' => true,
            'includeRoot' => false,
            'includeLeaf' => false,
            'all' => false,
            'valueField' => 'id'
        ]);
        $resolver->setAllowedTypes('recurse', 'bool');
        $resolver->setAllowedTypes('relative', 'bool');
        $resolver->setAllowedTypes('includeRoot', 'bool');
        $resolver->setAllowedTypes('includeLeaf', 'bool');
        $resolver->setAllowedTypes('all', 'bool');
        $resolver->setAllowedTypes('valueField', 'string');

        $resolver->setNormalizer('label', function (Options $options, $label) {
            if (null === $label || empty($label)) {
                $isMultiple = $options['multiple'];
                $translator = $options['translator'];

                $label = $isMultiple ? $translator->__('Categories') : $translator->__('Category');
            }

            return $label;
        });
        $resolver->setNormalizer('placeholder', function (Options $options, $placeholder) {
            if (!$options['required']) {
                if (null == $placeholder || empty($placeholder)) {
                    $isMultiple = $options['multiple'];
                    $translator = $options['translator'];

                    $placeholder = $isMultiple ? $translator->__('Choose categories') : $translator->__('Choose a category');
                }
            }

            return $placeholder;
        });
        $resolver->setNormalizer('choices', function (Options $options, $choices) {
            if (empty($choices)) {
                $choices = $this->getCategoryChoices($options);
            }

            return $choices;
        });
    }

    /**
* @inheritDoc
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
    private function getCategoryChoices($options)
    {
        $choices = [];
        $locale = $options['locale'];

        $recurse = isset($options['recurse']) ? $options['recurse'] : true;
        $relative = isset($options['relative']) ? $options['relative'] : true;
        $includeRoot = isset($options['includeRoot']) ? $options['includeRoot'] : false;
        $includeLeaf = isset($options['includeLeaf']) ? $options['includeLeaf'] : false;
        $all = isset($options['all']) ? $options['all'] : false;
        $valueField = isset($options['valueField']) ? $options['valueField'] : 'id';

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

            $choices[$indent . ' ' . $catName] = $cat[$valueField];
        }

        return $choices;
    }
}

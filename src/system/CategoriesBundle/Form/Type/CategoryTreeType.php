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

namespace Zikula\CategoriesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\CategoriesBundle\Form\DataTransformer\CategoryTreeTransformer;
use Zikula\CategoriesBundle\Repository\CategoryRepositoryInterface;

class CategoryTreeType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transformer = new CategoryTreeTransformer($this->categoryRepository);
        $builder->addModelTransformer($transformer);
    }

    public function getBlockPrefix(): string
    {
        return 'zikulacategoriesbundle_category_tree';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'locale' => 'en',
            'recurse' => true,
            'relative' => true,
            'includeRoot' => false,
            'includeLeaf' => false,
            'all' => false,
        ]);
        $resolver->setAllowedTypes('recurse', 'bool');
        $resolver->setAllowedTypes('relative', 'bool');
        $resolver->setAllowedTypes('includeRoot', 'bool');
        $resolver->setAllowedTypes('includeLeaf', 'bool');
        $resolver->setAllowedTypes('all', 'bool');

        $options['translator'] = $this->translator;
        $resolver->setNormalizer('label', static function (Options $options, $label) {
            if (null === $label || empty($label)) {
                $isMultiple = $options['multiple'];
                $translator = $options['translator'];

                $label = $isMultiple ? $translator->trans('Categories') : $translator->trans('Category');
            }

            return $label;
        });
        $resolver->setNormalizer('placeholder', static function (Options $options, $placeholder) {
            if (!$options['required']) {
                if (empty($placeholder)) {
                    $isMultiple = $options['multiple'];
                    $translator = $options['translator'];

                    $placeholder = $isMultiple ? $translator->trans('Choose categories') : $translator->trans('Choose a category');
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

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    private function getCategoryChoices(Options $options): array
    {
        $locale = $options['locale'];
        $recurse = $options['recurse'] ?? true;
        $includeRoot = $options['includeRoot'] ?? false;
        $includeLeaf = $options['includeLeaf'] ?? false;
        $all = $options['all'] ?? false;

        $rootCategory = $this->categoryRepository->find(1);
        $children = $this->categoryRepository->getChildren($rootCategory, !$recurse, null, 'ASC', $includeRoot);

        $choices = [];
        foreach ($children as $child) {
            if (($child['leaf'] && !$includeLeaf) || ('I' === $child['status'] && $all)) {
                continue;
            }
            $indent = $child['lvl'] > 0 ? str_repeat('--', $child['lvl']) : '';
            if (!empty($child['displayName'][$locale])) {
                $catName = $child['displayName'][$locale];
            } else {
                $catName = $child['name'];
            }
            $choices['|' . $indent . ' ' . $catName] = $child['id'];
        }

        return $choices;
    }
}

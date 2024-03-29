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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\CategoriesBundle\Entity\AbstractCategoryAssignment;
use Zikula\CategoriesBundle\Entity\Category;
use Zikula\CategoriesBundle\Entity\CategoryRegistry;
use Zikula\CategoriesBundle\Form\DataTransformer\CategoriesCollectionTransformer;
use Zikula\CategoriesBundle\Form\EventListener\CategoriesMergeCollectionListener;
use Zikula\CategoriesBundle\Repository\CategoryRegistryRepositoryInterface;
use Zikula\CategoriesBundle\Repository\CategoryRepositoryInterface;

class CategoriesType extends AbstractType
{
    public function __construct(
        private readonly CategoryRegistryRepositoryInterface $categoryRegistryRepository,
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $registries = $this->categoryRegistryRepository->findBy([
            'bundleName' => $options['bundle'],
            'entityName' => $options['entity'],
        ]);

        $request = $this->requestStack->getMainRequest();
        $locale = null !== $request ? $request->getLocale() : 'en';

        /** @var CategoryRegistry[] $registries */
        foreach ($registries as $registry) {
            $baseCategory = $registry->getCategory();
            $queryBuilderClosure = static function (CategoryRepositoryInterface $repo) use ($baseCategory, $options) {
                return $repo->getChildrenQueryBuilder($baseCategory, $options['direct']);
            };
            $choiceLabelClosure = static function (Category $category) use ($baseCategory, $locale) {
                $indent = str_repeat('--', $category->getLvl() - $baseCategory->getLvl() - 1);

                $categoryName = $category['displayName'][$locale] ?? $category['displayName']['en'];

                return (!empty($indent) ? '|' : '') . $indent . $categoryName;
            };

            $registryOptions = [
                'em' => $options['em'],
                'attr' => $options['attr'],
                'required' => $options['required'],
                'multiple' => $options['multiple'],
                'expanded' => $options['expanded'],
                'class' => Category::class,
                'choice_label' => $choiceLabelClosure,
                'query_builder' => $queryBuilderClosure,
            ];

            if ($options['showRegistryLabels']) {
                $registryOptions['label'] = $baseCategory['displayName'][$locale] ?? $baseCategory['displayName']['en'];
            } else {
                $registryOptions['label_attr'] = !$options['expanded'] ? ['class' => 'd-none'] : [];
            }

            $builder->add('registry_' . $registry->getId(), EntityType::class, $registryOptions);
        }

        if (null === $options['em']) {
            $options['em'] = $this->entityManager;
        }

        $builder->addViewTransformer(new CategoriesCollectionTransformer($options), true);
        $builder->addEventSubscriber(new CategoriesMergeCollectionListener());
    }

    public function getBlockPrefix(): string
    {
        return 'zikulacategoriesbundle_categories';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['entityCategoryClass', 'bundle', 'entity']);
        $resolver->setDefined(['attr', 'multiple', 'expanded', 'direct', 'required', 'em']);
        $resolver->setDefaults([
            'attr' => [],
            'multiple' => false,
            'expanded' => false,
            'direct' => true,
            'bundle' => '',
            'entity' => '',
            'entityCategoryClass' => '',
            'em' => null,
            'required' => false,
            'showRegistryLabels' => false,
        ]);
        $resolver->setAllowedTypes('attr', 'array');
        $resolver->setAllowedTypes('multiple', 'bool');
        $resolver->setAllowedTypes('expanded', 'bool');
        $resolver->setAllowedTypes('required', 'bool');
        $resolver->setAllowedTypes('direct', 'bool');
        $resolver->setAllowedTypes('bundle', 'string');
        $resolver->setAllowedTypes('entity', 'string');
        $resolver->setAllowedTypes('entityCategoryClass', 'string');
        $resolver->setAllowedTypes('em', [ObjectManager::class, 'null']);
        $resolver->setAllowedTypes('showRegistryLabels', 'bool');

        $resolver->addAllowedValues('entityCategoryClass', static function ($value) {
            return is_subclass_of($value, AbstractCategoryAssignment::class);
        });
    }
}

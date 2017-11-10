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

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRegistryRepositoryInterface;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;
use Zikula\CategoriesModule\Form\DataTransformer\CategoriesCollectionTransformer;
use Zikula\CategoriesModule\Form\EventListener\CategoriesMergeCollectionListener;

/**
 * Class CategoriesType
 */
class CategoriesType extends AbstractType
{
    /**
     * @var CategoryRegistryRepositoryInterface
     */
    private $categoryRegistryRepository;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * CategoriesType constructor.
     *
     * @param CategoryRegistryRepositoryInterface $categoryRegistryRepository
     * @param RequestStack $requestStack
     */
    public function __construct(
        CategoryRegistryRepositoryInterface $categoryRegistryRepository,
        RequestStack $requestStack
    ) {
        $this->categoryRegistryRepository = $categoryRegistryRepository;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $registries = $this->categoryRegistryRepository->findBy([
            'modname' => $options['module'],
            'entityname' => $options['entity']
        ]);

        $locale = $this->requestStack->getMasterRequest()->getLocale();

        /** @var CategoryRegistryEntity[] $registries */
        foreach ($registries as $registry) {
            $baseCategory = $registry->getCategory();
            $queryBuilderClosure = function (CategoryRepositoryInterface $repo) use ($baseCategory, $options) {
                return $repo->getChildrenQueryBuilder($baseCategory, $options['direct']);
            };
            $choiceLabelClosure = function (CategoryEntity $category) use ($baseCategory, $locale) {
                $indent = str_repeat('--', $category->getLvl() - $baseCategory->getLvl() - 1);

                $categoryName = isset($category['display_name'][$locale]) ? $category['display_name'][$locale] : $category['display_name']['en'];

                return (!empty($indent) ? '|' : '') . $indent . $categoryName;
            };

            $registryOptions = [
                'em' => $options['em'],
                'attr' => $options['attr'],
                'required' => $options['required'],
                'multiple' => $options['multiple'],
                'expanded' => $options['expanded'],
                'class' => CategoryEntity::class,
                'choice_label' => $choiceLabelClosure,
                'query_builder' => $queryBuilderClosure
            ];

            if ($options['showRegistryLabels']) {
                $registryOptions['label'] = isset($baseCategory['display_name'][$locale]) ? $baseCategory['display_name'][$locale] : $baseCategory['display_name']['en'];
            } else {
                $registryOptions['label_attr'] = !$options['expanded'] ? ['class' => 'hidden'] : [];
            }

            $builder->add('registry_' . $registry->getId(), EntityType::class, $registryOptions);
        }

        $builder->addViewTransformer(new CategoriesCollectionTransformer($options), true);
        $builder->addEventSubscriber(new CategoriesMergeCollectionListener());
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulacategoriesmodule_categories';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['entityCategoryClass', 'module', 'entity']);
        $resolver->setDefined(['attr', 'multiple', 'expanded', 'includeGrandChildren', 'direct', 'required', 'em']);
        $resolver->setDefaults([
            'attr' => [],
            'multiple' => false,
            'expanded' => false,
            'includeGrandChildren' => false, // @deprecated use 'direct'
            'direct' => true,
            'module' => '',
            'entity' => '',
            'entityCategoryClass' => '',
            'em' => null,
            'required' => false,
            'showRegistryLabels' => false
        ]);
        $resolver->setAllowedTypes('attr', 'array');
        $resolver->setAllowedTypes('multiple', 'bool');
        $resolver->setAllowedTypes('expanded', 'bool');
        $resolver->setAllowedTypes('required', 'bool');
        $resolver->setAllowedTypes('includeGrandChildren', 'bool'); // @deprecated use 'direct'
        $resolver->setAllowedTypes('direct', 'bool');
        $resolver->setAllowedTypes('module', 'string');
        $resolver->setAllowedTypes('entity', 'string');
        $resolver->setAllowedTypes('entityCategoryClass', 'string');
        $resolver->setAllowedTypes('em', [ObjectManager::class, 'null']);
        $resolver->setAllowedTypes('showRegistryLabels', 'bool');

        // remove this normalizer when the 'includeGrandChildren' option is removed
        $resolver->setNormalizer('direct', function (Options $options, $value) {
            return !($options['includeGrandChildren'] || !$value);
        });

        $resolver->addAllowedValues('entityCategoryClass', function ($value) {
            return is_subclass_of($value, AbstractCategoryAssignment::class);
        });
    }
}

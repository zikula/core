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
     * CategoriesType constructor.
     *
     * @param CategoryRegistryRepositoryInterface $categoryRegistryRepository
     */
    public function __construct(CategoryRegistryRepositoryInterface $categoryRegistryRepository)
    {
        $this->categoryRegistryRepository = $categoryRegistryRepository;
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

        /** @var CategoryRegistryEntity[] $registries */
        foreach ($registries as $registry) {
            $queryBuilderClosure = function(CategoryRepositoryInterface $repo) use ($registry, $options) {
                return $repo->getChildrenQueryBuilder($registry->getCategory(), $options['direct']);
            };
            $choiceLabelClosure = function(CategoryEntity $category) use ($registry) {
                $indent = str_repeat('--', $category->getLvl() - $registry->getCategory()->getLvl() - 1);

                return (!empty($indent) ? '|' : '') . $indent . $category->getName();
            };
            $builder->add('registry_' . $registry->getId(), EntityType::class,
                [
                    'em' => $options['em'],
                    'label_attr' => !$options['expanded'] ? ['class' => 'hidden'] : [],
                    'attr' => $options['attr'],
                    'required' => $options['required'],
                    'multiple' => $options['multiple'],
                    'expanded' => $options['expanded'],
                    'class' => CategoryEntity::class,
                    'choice_label' => $choiceLabelClosure,
                    'query_builder' => $queryBuilderClosure
                ]);
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
        $resolver->setDefined(['attr', 'multiple', 'expanded', 'direct', 'required', 'em']);
        $resolver->setDefaults([
            'attr' => [],
            'multiple' => false,
            'expanded' => false,
            'direct' => true,
            'module' => '',
            'entity' => '',
            'entityCategoryClass' => '',
            'em' => null,
            'required' => false
        ]);
        $resolver->setAllowedTypes('attr', 'array');
        $resolver->setAllowedTypes('multiple', 'bool');
        $resolver->setAllowedTypes('expanded', 'bool');
        $resolver->setAllowedTypes('required', 'bool');
        $resolver->setAllowedTypes('direct', 'bool');
        $resolver->setAllowedTypes('module', 'string');
        $resolver->setAllowedTypes('entity', 'string');
        $resolver->setAllowedTypes('entityCategoryClass', 'string');
        $resolver->setAllowedTypes('em', [ObjectManager::class, 'null']);

        $resolver->addAllowedValues('entityCategoryClass', function($value) {
            return is_subclass_of($value, AbstractCategoryAssignment::class);
        });
    }
}

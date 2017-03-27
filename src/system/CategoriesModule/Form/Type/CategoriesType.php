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

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRegistryRepositoryInterface;
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
            $categoryId = $registry->getCategory()->getId();
            // default behaviour
            $queryBuilderClosure = function (EntityRepository $repo) use ($categoryId) {
                //TODO: (move to)/use own entity repository
                return $repo->createQueryBuilder('e')
                            ->where('e.parent = :parentId')
                            ->setParameter('parentId', (int) $categoryId);
            };
            $choiceLabelClosure = function (CategoryEntity $category) {
                return $category->getName();
            };
            if (true === $options['includeGrandChildren']) {
                // perform one recursive iteration
                $queryBuilderClosure = function (EntityRepository $repo) use ($categoryId) {
                    //TODO: (move to)/use own entity repository
                    $categoryIds = $repo->createQueryBuilder('e')
                        ->select('e.id')
                        ->where('e.parent = :parentId')
                        ->setParameter('parentId', (int) $categoryId)
                        ->getQuery()
                        ->getResult();
                    $categoryIds[] = $categoryId;

                    return $repo->createQueryBuilder('e')
                                ->where('e.parent IN (:parentIds)')
                                ->setParameter('parentIds', $categoryIds);
                };
                $choiceLabelClosure = function (CategoryEntity $category) use ($categoryId) {
                    $isMainLevel = $category->getParent()->getId() == $categoryId;

                    $indent = $isMainLevel ? '' : '|--';

                    return $indent . ' ' . $category->getName();
                };
            }

            $builder->add(
                'registry_' . $registry->getId(),
                'Symfony\Bridge\Doctrine\Form\Type\EntityType',
                [
                    'em' => $options['em'],
                    'label_attr' => !$options['expanded'] ? ['class' => 'hidden'] : [],
                    'attr' => $options['attr'],
                    'required' => $options['required'],
                    'multiple' => $options['multiple'],
                    'expanded' => $options['expanded'],
                    'class' => 'Zikula\CategoriesModule\Entity\CategoryEntity',
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
        $resolver->setRequired(['entityCategoryClass', 'module', 'entity', 'em']);
        $resolver->setDefaults([
            'csrf_protection' => false,
            'attr' => [],
            'multiple' => false,
            'expanded' => false,
            'includeGrandChildren' => false,
            'module' => '',
            'entity' => '',
            'entityCategoryClass' => '',
            'em' => null,
            'required' => false
        ]);
        $resolver->setAllowedTypes('csrf_protection', 'bool');
        $resolver->setAllowedTypes('attr', 'array');
        $resolver->setAllowedTypes('multiple', 'bool');
        $resolver->setAllowedTypes('expanded', 'bool');
        $resolver->setAllowedTypes('required', 'bool');
        $resolver->setAllowedTypes('includeGrandChildren', 'bool');
        $resolver->setAllowedTypes('module', 'string');
        $resolver->setAllowedTypes('entity', 'string');
        $resolver->setAllowedTypes('entityCategoryClass', 'string');
        $resolver->setAllowedTypes('em', 'Doctrine\Common\Persistence\ObjectManager');

        $resolver->addAllowedValues('entityCategoryClass', function ($value) {
            return is_subclass_of($value, 'Zikula\CategoriesModule\Entity\AbstractCategoryAssignment');
        });
    }
}

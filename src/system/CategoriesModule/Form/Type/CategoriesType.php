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
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\CategoriesModule\Api\CategoryRegistryApi;
use Zikula\CategoriesModule\Form\DataTransformer\CategoriesCollectionTransformer;
use Zikula\CategoriesModule\Form\EventListener\CategoriesMergeCollectionListener;

/**
 * Class CategoriesType
 */
class CategoriesType extends AbstractType
{
    /**
     * @var CategoryRegistryApi
     */
    private $categoryRegistryApi;

    /**
     * CategoriesType constructor.
     *
     * @param CategoryRegistryApi $categoryRegistryApi CategoryRegistryApi service instance
     */
    public function __construct(CategoryRegistryApi $categoryRegistryApi)
    {
        $this->categoryRegistryApi = $categoryRegistryApi;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (['entityCategoryClass', 'module', 'entity'] as $requiredOptionName) {
            if (empty($options[$requiredOptionName])) {
                throw new MissingOptionsException(sprintf('Missing required option: %s', $requiredOptionName));
            }
        }
        $registries = $this->categoryRegistryApi->getModuleCategoryIds($options['module'], $options['entity'], 'id');

        foreach ($registries as $registryId => $categoryId) {
            $builder->add(
                'registry_' . $registryId,
                'Symfony\Bridge\Doctrine\Form\Type\EntityType',
                [
                    'em' => $options['em'],
                    'label_attr' => ['class' => 'hidden'],
                    'attr' => $options['attr'],
                    'required' => $options['required'],
                    'multiple' => $options['multiple'],
                    'class' => 'Zikula\CategoriesModule\Entity\CategoryEntity',
                    'property' => 'name',
                    'query_builder' => function (EntityRepository $repo) use ($categoryId) {
                        //TODO: (move to)/use own entity repository
                        return $repo->createQueryBuilder('e')
                                    ->where('e.parent = :parentId')
                                    ->setParameter('parentId', (int) $categoryId);
                    }
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
        return 'categories';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'attr' => [],
            'multiple' => false,
            'module' => '',
            'entity' => '',
            'entityCategoryClass' => '',
            'em' => null
        ]);
    }
}

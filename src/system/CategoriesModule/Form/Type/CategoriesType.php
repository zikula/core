<?php
/**
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
use Zikula\CategoriesModule\Form\DataTransformer\CategoriesCollectionTransformer;
use Zikula\CategoriesModule\Form\EventListener\CategoriesMergeCollectionListener;

/**
 * Class CategoriesType
 * @package Zikula\CategoriesModule\Form\Type
 */
class CategoriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['entityCategoryClass']) || empty($options['module']) || empty($options['entity'])) {
            throw new \InvalidArgumentException('empty argument!');
        }

        $registries = \CategoryRegistryUtil::getRegisteredModuleCategories($options['module'], $options['entity'], 'id');

        foreach ($registries as $registryId => $categoryId) {
            $builder->add(
                'registry_' . $registryId,
                'entity',
                [
                    'attr' => $options['attr'],
                    'required' => $options['required'],
                    'multiple' => $options['multiple'],
                    'class' => 'ZikulaCategoriesModule:CategoryEntity',
                    'property' => 'name',
                    'query_builder' => function (EntityRepository $repo) use ($categoryId) {
                        //TODO: (move to)/use own entity repository after CategoryUtil migration
                        return $repo->createQueryBuilder('e')
                                    ->where('e.parent = :parentId')
                                    ->setParameter('parentId', (int) $categoryId);
                    }
                ]);
        }

        $builder->addViewTransformer(new CategoriesCollectionTransformer($options), true);
        $builder->addEventSubscriber(new CategoriesMergeCollectionListener());
    }

    public function getBlockPrefix()
    {
        return 'categories';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'attr' => [],
            'multiple' => false,
            'module' => '',
            'entity' => '',
            'entityCategoryClass' => ''
        ]);
    }
}

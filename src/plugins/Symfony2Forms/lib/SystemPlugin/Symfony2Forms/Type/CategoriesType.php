<?php

namespace SystemPlugin\Symfony2Forms\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;
use SystemPlugin\Symfony2Forms\EventListener\CategoriesMergeCollectionListener;
use SystemPlugin\Symfony2Forms\DataTransformer\CategoriesCollectionTransformer;

class CategoriesType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->prependClientTransformer(new CategoriesCollectionTransformer($options['entityCategoryClass']))
                ->addEventSubscriber(new CategoriesMergeCollectionListener());
        
        $registries = \CategoryRegistryUtil::getRegisteredModuleCategories($options['module'], $options['entity'], 'id');
        
        foreach($registries as $registryId => $categoryId) {
            $builder->add(
                    'registry_' . $registryId, 
                    'entity', 
                    array(
                        'class' => 'Zikula_Doctrine2_Entity_Category',
                        'property' => 'name',
                        'query_builder' => function(EntityRepository $repo) use($categoryId) {
                            //TODO: (move to)/use own entity repository after CategoryUtil migration
                            return $repo->createQueryBuilder('e')
                                        ->where('e.parent = :parentId')
                                        ->setParameter('parentId', (int) $categoryId);
                        }
                    ));
        }
    }

    public function getName()
    {
        return 'categories';
    }
    
    public function getDefaultOptions(array $options) {
        return array('module' => $options['module'],
                     'entity' => $options['entity'],
                     'entityCategoryClass' => $options['entityCategoryClass']);
    }
}


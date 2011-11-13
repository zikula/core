<?php

class ExampleDoctrine_Form_CategoriesType extends Symfony\Component\Form\AbstractType
{
    public function buildForm(Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->prependClientTransformer(new ExampleDoctrine_Form_CategoriesType_Transformer($options['entityCategoryClass']))
                ->addEventSubscriber(new ExampleDoctrine_Form_CategoriesType_MergeCollectionListener());
        
        $registries = CategoryRegistryUtil::getRegisteredModuleCategories($options['module'], $options['entity'], 'id');
        
        foreach($registries as $registryId => $categoryId) {
            $builder->add(
                    'registry_' . $registryId, 
                    'entity', 
                    array(
                        'class' => 'Zikula_Doctrine2_Entity_Category',
                        'property' => 'name',
                        'query_builder' => function(Doctrine\ORM\EntityRepository $repo) use($categoryId) {
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

class ExampleDoctrine_Form_CategoriesType_Transformer implements \Symfony\Component\Form\DataTransformerInterface 
{
    private $cls;
    
    public function __construct($cls)
    {
        $this->cls = $cls;
    }
    
    public function reverseTransform($value)
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();
        $class = $this->cls;
        
        foreach($value as $regId => $category) {
            $regId = (int) substr($regId, strpos($regId, '_') + 1);
            $collection->set($regId, new $class($regId, $category, null));
        }
        
        return $collection;
    }
    
    public function transform($value)
    {
        if(!$value instanceof \Doctrine\Common\Collections\Collection) {
            return null;
        }
        
        $data = array();
        
        foreach($value as $key => $entityCategory) {
            $data['registry_' . $key] = $entityCategory->getCategory();
        }
        
        return $data;
    }
}

class ExampleDoctrine_Form_CategoriesType_MergeCollectionListener implements Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return array(\Symfony\Component\Form\FormEvents::BIND_NORM_DATA => 'onBindNormData');
    }

    public function onBindNormData(FilterDataEvent $event)
    {
        $collection = $event->getForm()->getData();
        $data = $event->getData();
        $rootEntity = $event->getForm()->getParent()->getData();

        if (!$collection) {
            $collection = $data;
            
            foreach($data as $key => $value) {
                $value->setEntity($rootEntity);
            }
        } else if (count($data) === 0) {
            $collection->clear();
        } else {
            // merge $data into $collection
            foreach ($collection as $key => $entity) {
                if (!$data->containsKey($key)) {
                    $collection->removeElement($entity);
                } else {
                    $collection->get($key)->setCategory($data->get($key)->getCategory());
                    $data->remove($key);
                }
            }

            foreach ($data as $key => $entity) {
                $collection->set($key, $entity);
                $entity->setEntity($rootEntity);
            }
        }

        $event->setData($collection);
    }
}


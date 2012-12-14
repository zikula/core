<?php

namespace Zikula\Core\Forms\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\FilterDataEvent;

class CategoriesMergeCollectionListener implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return array(FormEvents::BIND_NORM_DATA => 'onBindNormData');
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

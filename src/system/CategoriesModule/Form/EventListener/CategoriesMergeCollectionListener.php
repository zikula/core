<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\CategoriesModule\Form\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class CategoriesMergeCollectionListener
 * @package Zikula\CategoriesModule\Form\EventListener
 */
class CategoriesMergeCollectionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::SUBMIT => 'onBindNormData');
    }

    public function onBindNormData(FormEvent $event)
    {
        $submittedData = $event->getData();
        $rootEntity = $event->getForm()->getParent()->getData();

        $collection = new ArrayCollection();
        foreach ($submittedData as $categoryCollectionByRegistry) {
            foreach ($categoryCollectionByRegistry as $category) {
                $category->setEntity($rootEntity);
                $collection->add($category);
            }
        }

        $event->setData($collection);
    }
}

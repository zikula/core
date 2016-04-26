<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        return [
            FormEvents::SUBMIT => 'onBindNormData'
        ];
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

<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Form\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

class CategoriesMergeCollectionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'onBindNormData'
        ];
    }

    /**
     * This listener sets the value for Entity on each AbstractCategoryAssignment that has been submitted
     * because it cannot be done when reverse transforming the data
     * @see \Zikula\CategoriesModule\Form\DataTransformer\CategoriesCollectionTransformer
     */
    public function onBindNormData(FormEvent $event): void
    {
        $submittedData = $event->getData();
        $rootEntity = $event->getForm()->getParent()->getData();

        $collection = new ArrayCollection();
        foreach ($submittedData as $categoryCollectionByRegistry) {
            /** @var AbstractCategoryAssignment $categoryAssignment */
            foreach ($categoryCollectionByRegistry as $categoryAssignment) {
                $categoryAssignment->setEntity($rootEntity);
                $collection->add($categoryAssignment);
            }
        }

        $event->setData($collection);
    }
}

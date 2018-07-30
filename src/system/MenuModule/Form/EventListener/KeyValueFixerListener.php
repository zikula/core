<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class KeyValueFixerListener
 *
 * From http://stackoverflow.com/q/19158224/2600812 and I agree this feels 'hacky' but it works.
 *
 * This listener performs a proper 'transform' on the form data for the collectionType that the DataTransformer appears
 * unable to perform correctly.
 * Where the value is an array, the array is json_encoded first.
 */
class KeyValueFixerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData', 1],
            // priority of 1 ensures listener is triggered BEFORE ResizeFormListener and therefore only effects reverse transform.
        ];
    }

    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();
        $result = [];
        if ($data) {
            foreach ($data as $key => $value) {
                $result[] = [
                    'key' => $key,
                    'value' => is_array($value) ? json_encode($value) : $value,
                ];
            }
        }
        $event->setData($result);
    }
}

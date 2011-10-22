<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\EventListener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\FilterDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Trims string data
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class TrimListener implements EventSubscriberInterface
{
    public function onBindClientData(FilterDataEvent $event)
    {
        $data = $event->getData();

        if (is_string($data)) {
            $event->setData(trim($data));
        }
    }

    static public function getSubscribedEvents()
    {
        return array(FormEvents::BIND_CLIENT_DATA => 'onBindClientData');
    }
}

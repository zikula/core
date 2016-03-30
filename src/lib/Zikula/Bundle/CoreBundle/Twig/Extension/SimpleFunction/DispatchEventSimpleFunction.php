<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension\SimpleFunction;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Core\Event\GenericEvent;

class DispatchEventSimpleFunction
{
    private $dispatcher;

    /**
     * DispatchEventSimpleFunction constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->dispatcher = $eventDispatcher;
    }

    /**
     * @param string $name
     * @param GenericEvent|null $providedEvent
     * @param null $subject
     * @param array $arguments
     * @param null $data
     * @return mixed
     */
    public function dispatchEvent($name, GenericEvent $providedEvent = null, $subject = null, array $arguments = [], $data = null)
    {
        $event = isset($providedEvent) ? $providedEvent : new GenericEvent($subject, $arguments, $data);
        $this->dispatcher->dispatch($name, $event);

        return $event->getData();
    }
}

<?php
/**
 * Copyright 2009 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher as EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/**
 * Zikula_EventManager.
 *
 * Manages event handlers and invokes them for notified events.
 *
 * @deprecated from 1.4
 * @use \Symfony\Component\EventDispatcher\EventDispatcher
 */
class Zikula_EventManager extends EventDispatcher
{
    /**
     * Attach an event handler to the stack.
     *
     * @param string  $name     Name of handler.
     * @param mixed   $handler  Callable handler or instance of ServiceHandler.
     * @param integer $priority Priority to control notification order, (default = 10).
     *
     * @throws InvalidArgumentException If Handler is not callable or an instance of ServiceHandler.
     *
     * @return void
     */
    public function attach($name, $handler, $priority=10)
    {
        // using this method will adjust the listener priority automatically for
        // Sf Event Dispatcher where higher is executed first.
        $priority = 0-(int)$priority;

        if ($handler instanceof Zikula_ServiceHandler) {
            $callable = array($handler->getId(), $handler->getMethodName());
            $this->addListenerService($name, $callable, $priority);
        }

        $this->addListener($name, $handler, $priority);
    }

    /**
     * Removed a handler from the stack.
     *
     * @param string   $name    Handler name.
     * @param callable $handler Callable handler.
     *
     * @return void
     */
    public function detach($name, $handler)
    {
        $this->removeListener($name, $handler);
    }

    /**
     * Notify all handlers for given event name but stop if signalled.
     *
     * @param Event $event Event.
     *
     * @return Event
     */
    public function notify(Event $event)
    {
        return $this->dispatch($event->getName(), $event);
    }

    /**
     * Dispatch event.
     *
     * @param string                                  $name
     * @param Symfony\Component\EventDispatcher\Event $event
     *
     * @return Zikula_Event
     */
    public function dispatch($name, Event $event = null)
    {
        if (null === $event) {
            $event = new Zikula_Event();
        }

        return parent::dispatch($name, $event);
    }

    /**
     * Flush handlers.
     *
     * Clears all handlers.
     *
     * @return void
     */
    public function flushHandlers()
    {
        $array = $this->getListeners();
        foreach ($array as $name => $callable) {
            $this->removeListener($name, $callable);

        }
    }

    /**
     * Getter for the serviceManager property.
     *
     * @throws LogicException If no ServiceManager exists.
     *
     * @return Zikula_ServiceManager instance.
     */
    public function getServiceManager()
    {
        return $this->getContainer();
    }

    /**
     * Has this got a ServiceManager.
     *
     * @return boolean
     */
    public function hasServiceManager()
    {
        return (bool)$this->getContainer();
    }
}

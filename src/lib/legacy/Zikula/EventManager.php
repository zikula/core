<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher as EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/**
 * Zikula_EventManager.
 *
 * Manages event handlers and invokes them for notified events.
 *
 * @deprecated since 1.4.0
 * @see \Symfony\Component\EventDispatcher\EventDispatcher
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
     * @deprecated since 1.4.0
     *
     * @return void
     */
    public function attach($name, $handler, $priority = 10)
    {
        // using this method will adjust the listener priority automatically for
        // Sf Event Dispatcher where higher is executed first.
        $priority = 0 - (int)$priority;

        if ($handler instanceof Zikula_ServiceHandler) {
            $callable = [$handler->getId(), $handler->getMethodName()];

            return $this->addListenerService($name, $callable, $priority);
        }

        $this->addListener($name, $handler, $priority);
    }

    /**
     * Removed a handler from the stack.
     *
     * @param string   $name    Handler name.
     * @param callable $handler Callable handler.
     *
     * @deprecated since 1.4.0
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
     * @param string $name  Event name.
     * @param Event  $event Event object, null creates new Event
     *
     * @deprecated since 1.4.0
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
        $dispatcher = func_num_args() === 3 ? func_get_arg(2) : null;

        return parent::dispatch($name, $event, $dispatcher);
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
     * @deprecated since 1.4.0
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
     * @deprecated since 1.4.0
     *
     * @return boolean
     */
    public function hasServiceManager()
    {
        return (bool)$this->getContainer();
    }
}

<?php
/**
 * Copyright 2009 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Common\EventManager;
use Zikula\Common\ServiceManager\ServiceManager;
use Zikula\Common\EventManager\ServiceHandler;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * EventManager.
 *
 * Manages event handlers and invokes them for notified events.
 */
class ServiceManagerAwareEventManager extends EventManager implements EventManagerInterface
{
    /**
     * ServiceManager object.
     *
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var array
     */
    private $serviceHandlers = array();

    /**
     * Constructor.
     *
     * Inject an instance of ServiceManager to enable lazy loading of event handlers.
     *
     * @param ServiceManager $serviceManager Service manager instance.
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Attach an event handler to the stack.
     *
     * @param string  $name     Name of handler.
     * @param mixed   $listener Callable handler or instance of ServiceHandler.
     * @param integer $priority Priotity to control notification order, (default = 0).
     *
     * @throws \InvalidArgumentException If Handler is not callable or an instance of ServiceHandler.
     *
     * @deprecated Use addListener, and addServiceListener
     *
     * @return void
     */
    public function attach($name, $listener, $priority=0)
    {
        $priority = (int)$priority;

        if ($listener instanceof ServiceHandler) {
            return $this->attachListenerService($name, $listener, $priority);
        }

        if (!is_callable($listener)) {
            // an error has occurred, so we'll generate a meaningful message.
            if (is_array($listener)) {
                $callableText = is_object($listener[0]) ? get_class($listener[0]) . "->$listener[1]()" : "$listener[0]::$listener[1]()";
            } else {
                $callableText = "$listener()";
            }

            throw new \InvalidArgumentException(sprintf('Event listener %s given is not a valid PHP callback', $callableText));
        }

        parent::addListener($name, $listener, $priority);
    }

    /**
     * Attach a service handler as an event listener.
     *
     * @param string         $name           Event name.
     * @param ServiceHandler $serviceHandler ServiceHandler (serviceID, Method)
     * @param string         $priority       Higher get's executed first, default = 0.
     */
    public function attachListenerService($name, ServiceHandler $serviceHandler, $priority=0)
    {
        if (!$this->serviceManager->hasService($serviceHandler->getId())) {
            throw new \InvalidArgumentException(sprintf('ServiceHandler (id:"%s") is not registered with the ServiceManager', $serviceHandler->getId()));
        }

        $priority = (int)$priority;
        $this->serviceHandlers[$name][] = array($serviceHandler, $priority);
    }

    /**
     * Removed a listener from the stack.
     *
     * @param string   $eventName Listener name.
     * @param callable $listener  Callable handler.
     *
     * @deprecated Use removeListener
     *
     * @return void
     */
    public function detach($eventName, $listener)
    {
        parent::removeListener($eventName, $listener);
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

    public function dispatch($name, SymfonyEvent $event = null)
    {
        if (!$event) {
            $event = new Event();
        }

        $event->setName($name);
        $event->setEventManager($this);

        $this->addServiceListeners($event->getName());

        parent::dispatch($event->getName(), $event);

        return $event;
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
        foreach ($this->getListeners() as $eventName => $listener) {
            if (isset($this->serviceHandlers[$eventName])) {
                unset($this->serviceHandlers[$eventName]);
            }

            parent::removeListener($eventName, $listener);
        }
    }

    private function addServiceListeners($name)
    {
        if (!isset($this->serviceHandlers[$name])) {
            return;
        }

        foreach ($this->serviceHandlers[$name] as $args) {
            list($serviceHandler, $priority) = $args;
            $service = $this->serviceManager->getService($serviceHandler->getId());
            $method = $serviceHandler->getMethodName();
            parent::addListener($name, array($service, $method), $priority);
            unset($this->serviceHandlers[$name]);
        }
    }

    /**
     * Getter for the serviceManager property.
     *
     * @return ServiceManager instance.
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}

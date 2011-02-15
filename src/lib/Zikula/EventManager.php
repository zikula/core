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

/**
 * Zikula_EventManager.
 *
 * Manages event handlers and invokes them for notified events.
 */
class Zikula_EventManager
{

    /**
     * Storage for handlers.
     *
     * @var array
     */
    protected $handlers;

    /**
     * ServiceManager object.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * Constructor.
     *
     * Inject an instance of ServiceManager to enable lazy loading of event handlers.
     *
     * @param Zikula_ServiceManager $serviceManager Optional service manager instance.
     */
    public function __construct(Zikula_ServiceManager $serviceManager = null)
    {
        $this->serviceManager = $serviceManager;
        $this->handlers = array();
    }

    /**
     * Attach an event handler to the stack.
     *
     * @param string  $name    Name of handler.
     * @param mixed   $handler Callable handler or instance of ServiceHandler.
     * @param integer $weight  Handler weight to control invokation order, (default = 10).
     *
     * @throws InvalidArgumentException If Handler is not callable or an instance of ServiceHandler.
     *
     * @return void
     */
    public function attach($name, $handler, $weight=10)
    {
        if ($handler instanceof Zikula_ServiceHandler && !$this->serviceManager->hasService($handler->getId())) {
            throw new InvalidArgumentException(sprintf('ServiceHandler (id:"%s") is not registered with the ServiceManager', $handler->getId()));
        }

        $weight = (integer)$weight;
        if (!$handler instanceof Zikula_ServiceHandler && !is_callable($handler)) {
            if (is_array($handler)) {
                $callableText = is_object($handler[0]) ? get_class($handler[0]) . "->$handler[1]()" : "$handler[0]::$handler[1]()";
            } else {
                $callableText = "$handler()";
            }
            throw new InvalidArgumentException(sprintf('Handler %s given is not a valid PHP callback or ServiceHandler instance', $callableText));
        }

        if (!isset($this->handlers[$name][$weight])) {
            $this->handlers[$name][$weight] = array();
        }

        $this->handlers[$name][$weight][] = $handler;

        // Reorder according to priority.
        ksort($this->handlers[$name]);
        $this->handlers[$name] = $this->handlers[$name];
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
        if (!$this->existsHandler($name)) {
            return;
        }

        foreach ($this->handlers[$name] as $weight => $handlersArray) {
            foreach (array_keys($handlersArray) as $key) {
                if ($this->handlers[$name][$weight][$key] === $handler) {
                    // Remove handler.
                    unset($this->handlers[$name][$weight][$key]);
                }
            }

            if (empty($this->handlers[$name][$weight])) {
                // If there are no handers for this weight, remove key.
                unset($this->handlers[$name][$weight]);
            }
        }

        // If there are no more handlers for this name, remove key.
        if (empty($this->handlers[$name])) {
            unset ($this->handlers[$name]);
        }
    }

    /**
     * Getter for handlers property.
     *
     * @return array Handlers.
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Invoke all registered handlers.
     *
     * @param Zikula_Event $event Event.
     *
     * @return object Zikula_Event $event.
     */
    public function notify(Zikula_Event $event)
    {
        $handlers = $this->extractHandlers($event->getName());
        if ($handlers) {
            foreach ($handlers as $handler) {
                $this->invoke($handler, $event);
            }
        }

        return $event;
    }

    /**
     * Invoke all handlers until one responds true.
     *
     * @param Zikula_Event $event Event.
     *
     * @return object Zikula_Event $event.
     */
    public function notifyUntil(Zikula_Event $event)
    {
        $handlers = $this->extractHandlers($event->getName());
        if ($handlers) {
            foreach ($handlers as $handler) {
                $this->invoke($handler, $event);
                if ($event->hasNotified()) {
                    // halt execution because someone answered
                    break;
                }
            }
        }

        return $event;
    }

    /**
     * Extracts handlers according to set priority.
     *
     * @param string $name Event handler name.
     *
     * @return array Non associative array of handlers, empty array if none were found.
     */
    public function extractHandlers($name)
    {
        $handlers = array();
        if ($this->existsHandler($name)) {
            foreach ($this->handlers[$name] as $callables) {
                $handlers = array_merge($handlers, $callables);
            }
        }

        return $handlers;
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
        $this->handlers = array();
    }

    /**
     * Invoke handler.
     *
     * @param callable     $handler Callable by PHP.
     * @param Zikula_Event $event   Event object.
     *
     * @return void
     */
    protected function invoke($handler, $event)
    {
        if ($handler instanceof Zikula_ServiceHandler) {
            $service = $this->serviceManager->getService($handler->getId());
            $reflectionMethod = new ReflectionMethod(get_class($service), $handler->getMethodName());
            $reflectionMethod->invoke($service, $event);
        } else {
            call_user_func($handler, $event);
        }
    }

    /**
     * Return true if event handler $name exists.
     *
     * @param string $name Handler name.
     *
     * @return boolean
     */
    public function existsHandler($name)
    {
        return array_key_exists($name, $this->handlers);
    }

    /**
     * Getter for the serviceManager property.
     *
     * @throws LogicException If no ServiceManager exists.
     *
     * @return object ServiceManager instance.
     */
    public function getServiceManager()
    {
        if (!$this->serviceManager) {
            throw new LogicException('No ServiceManager was registered with this EventManager instance at construction.');
        }
        return $this->serviceManager;
    }
}


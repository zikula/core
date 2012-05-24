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
class Zikula_EventManager implements Zikula_EventManagerInterface
{
    /**
     * Storage for handlers.
     *
     * @var array
     */
    private $handlers;

    /**
     * ServiceManager object.
     *
     * @var Zikula_ServiceManager
     */
    private $serviceManager;

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
     * @param string  $name     Name of handler.
     * @param mixed   $handler  Callable handler or instance of ServiceHandler.
     * @param integer $priority Priotity to control notification order, (default = 10).
     *
     * @throws InvalidArgumentException If Handler is not callable or an instance of ServiceHandler.
     *
     * @return void
     */
    public function attach($name, $handler, $priority=10)
    {
        if ($handler instanceof Zikula_ServiceHandler && $this->serviceManager && !$this->serviceManager->hasService($handler->getId())) {
            throw new InvalidArgumentException(sprintf('ServiceHandler (id:"%s") is not registered with the ServiceManager', $handler->getId()));
        }

        $priority = (int)$priority;
        if (!$handler instanceof Zikula_ServiceHandler && !is_callable($handler)) {
            // an error has occurred, so we'll generate a meaningful message.
            if (is_array($handler)) {
                $callableText = is_object($handler[0]) ? get_class($handler[0]) . "->$handler[1]()" : "$handler[0]::$handler[1]()";
            } else {
                $callableText = "$handler()";
            }
            throw new InvalidArgumentException(sprintf('Handler %s given is not a valid PHP callback or Zikula_ServiceHandler instance.', $callableText));
        }

        if (!isset($this->handlers[$name][$priority])) {
            $this->handlers[$name][$priority] = array();
        }

        $this->handlers[$name][$priority][] = $handler;

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
        if (!$this->hasHandlers($name)) {
            return;
        }

        foreach ($this->handlers[$name] as $priority => $handlersArray) {
            foreach (array_keys($handlersArray) as $key) {
                if ($this->handlers[$name][$priority][$key] === $handler) {
                    // Remove handler.
                    unset($this->handlers[$name][$priority][$key]);
                }
            }

            if (empty($this->handlers[$name][$priority])) {
                // If there are no handers for this weight, remove key.
                unset($this->handlers[$name][$priority]);
            }
        }

        // If there are no more handlers for this name, remove key.
        if (empty($this->handlers[$name])) {
            unset ($this->handlers[$name]);
        }
    }

    /**
     * Notify all handlers for given event name but stop if signalled.
     *
     * @param Zikula_EventInterface $event Event.
     *
     * @return Zikula_EventInterface
     */
    public function notify(Zikula_EventInterface $event)
    {
        $event->setEventManager($this);
        $handlers = $this->getHandlers($event->getName());
        foreach ($handlers as $handler) {
            $this->invoke($handler, $event);
            if ($event->isStopped()) {
                // stop signal was received from event, so stop.
                break;
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
    private function getHandlers($name)
    {
        $handlers = array();
        if ($this->hasHandlers($name)) {
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
     * @param callable              $handler Callable by PHP.
     * @param Zikula_EventInterface $event   Event object.
     *
     * @return void
     */
    private function invoke($handler, Zikula_EventInterface $event)
    {
        if ($handler instanceof Zikula_ServiceHandler) {
                $service = $this->serviceManager->getService($handler->getId());
                $method = $handler->getMethodName();
                // invoke service method
                $service->$method($event);
        } else {
            if (is_array($handler)) {
                // PHP callable format
                if (is_object($handler[0])) {
                    // invoke instanciated object method.
                    $handler[0]->$handler[1]($event);
                } else {
                    // invoke static class method.
                    $handler[0]::$handler[1]($event);
                }
            } else {
                // invoke function including anonymous functions
                $handler($event);
            }
        }
    }

    /**
     * Return true if event handler $name exists.
     *
     * @param string $name Handler name.
     *
     * @return boolean
     */
    private function hasHandlers($name)
    {
        return array_key_exists($name, $this->handlers);
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
        if (!$this->serviceManager) {
            throw new RuntimeException('No ServiceManager was registered with this EventManager at construction.');
        }

        return $this->serviceManager;
    }

    /**
     * Has this got a ServiceManager.
     *
     * @return boolean
     */
    public function hasServiceManager()
    {
        return (bool)$this->serviceManager;
    }
}

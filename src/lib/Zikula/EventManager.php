<?php
/**
 * Copyright 2009 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
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
     * @var object
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
     * @param string $name    Name of handler.
     * @param mixed  $handler Callable handler or instance of ServiceHandler.
     *
     * @throws InvalidArgumentException If Handler is not callable or an instance of ServiceHandler.
     *
     * @return void
     */
    public function attach($name, $handler)
    {
        if ($handler instanceof Zikula_ServiceHandler && !$this->serviceManager->hasService($handler->getId())) {
            throw new InvalidArgumentException(sprintf('ServiceHandler (id:"%s") is not registered with the ServiceManager', $handler->getId()));
        }

        if (!$handler instanceof Zikula_ServiceHandler && !is_callable($handler)) {
            throw new InvalidArgumentException('Handler given is not a valid PHP callback or ServiceHandler instance');
        }

        $this->handlers[$name][] = $handler;
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
        if (isset($this->handlers[$name])) {
            // save handlers
            $handlers = $this->handlers[$name];
            unset($this->handlers[$name]);
            foreach ($handlers as $test) {
                // rebuild array of handles minus the one we detached
                if ($handler !== $test) {
                    $this->handlers[$name][] = $test;
                }
            }
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
        $name = $event->getName();
        if ($this->existsHandler($name)) {
           foreach ($this->handlers[$name] as $handler) {
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
    public function notifyUntil(Zikula_Event $event, $value = null)
    {
        $name = $event->getName();
        if ($this->existsHandler($name)) {
            foreach ($this->handlers[$name] as $handler) {
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
     * Invoke handler.
     *
     * @param callable $handler Callable by PHP.
     * @param Zikula_Event    $event   Event object.
     *
     * @return boolean
     */
    protected function invoke($handler, $event)
    {
        if ($handler instanceof Zikula_ServiceHandler) {
            $service = $this->serviceManager->getService($handler->getId());
            $reflectionMethod = new ReflectionMethod(get_class($service), $handler->getMethodName());
            return $reflectionMethod->invoke($service, $event);
        } else {
            return call_user_func($handler, $event);
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
        return (array_key_exists($name, $this->handlers) && count($this->handlers[$name] > 0) ? true : false);
    }
}


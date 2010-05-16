<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * EventManager.
 *
 * Manages event handlers and invokes them for notified events.
 */
class EventManager
{

    /**
     * Storage for handlers.
     *
     * @var array
     */
    protected $handlers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->handlers = array();
    }

    /**
     * Attach an event handler to the stack.
     *
     * @param string $name    Name of handler.
     * @param mixed  $handler Callable handler.
     *
     * @throws InvalidArgumentException If Handler is not callable.
     *
     * @return void
     */
    public function attach($name, $handler)
    {
        if (!is_callable($handler)) {
            throw new InvalidArgumentException('Handler given is not a valid PHP callback.');
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
     * @param Event $event Event.
     *
     * @return object Event $event.
     */
    public function notify(Event $event)
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
     * @param Event $event Event.
     *
     * @return object Event $event.
     */
    public function notifyUntil(Event $event, $value = null)
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
     * @param Event    $event   Event object.
     *
     * @return boolean
     */
    protected function invoke($handler, $event)
    {
        return call_user_func($handler, $event);
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


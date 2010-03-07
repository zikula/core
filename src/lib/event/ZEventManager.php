<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * ZEventManager
 *
 * Manages event handlers and invokes them for notified events
 *
 */
class ZEventManager
{
    // storage
    protected $handlers;

    /**
     * constructor
     */
    function __construct()
    {
        $this->handlers = array();
    }

    /**
     * Attach an event handler to the stack
     *
     * @param string $name
     * @param callback $handler
     */
    public function attach($name, $handler)
    {
        if (!is_callable($handler)) {
            throw new InvalidArgumentException('Handler is not a valid callback.');
        }
        $this->handlers[$name][] = $handler;
    }

    /**
     * Removed a handler from the stack
     *
     * @param string $name
     * @param callback $handler
     */
    public function detach($name, $handler)
    {
        if (isset($this->handlers[$name])) {
            $handlers = $this->handlers[$name];
            unset($this->handlers[$name]);
            foreach ($handlers as $test) {
                if ($handler !== $test) {
                    $this->handlers[$name][] = $test;
                }
            }
        }
    }

    /**
     * Invoke all registered handlers
     *
     * @param ZEvent $event
     * @return ZEvent $event
     */
    public function notify(ZEvent $event)
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
     * Invoke all handlers until one responds true
     *
     * @param ZEvent $event
     * @return ZEvent $event
     */
    public function notifyUntil(ZEvent $event)
    {
        $name = $event->getName();
        if ($this->existsHandler($name)) {
            foreach ($this->handlers[$name] as $handler) {
                if ($this->invoke($handler, $event)) {
                    $event->flagNotified();
                    break;
                }
            }
        }
        return $event;
    }

    /**
     * Chain load all handlers to process passed args feeding response from one
     * to the next
     *
     * @param ZEvent $event
     * @param mixed $args
     *
     * @return ZEvent $event
     */
    public function process(ZEvent $event, $args)
    {
        $name = $event->getName();
        if ($this->existsHandler($name)) {
            $sourceType = gettype($args);
            foreach ($this->handlers[$name] as $handler) {
                $result = $this->invoke($handler, $event, $args);
                // validate return
                if (gettype($result) == $sourceType) {
                    $args = $result;
                } else {
                    throw new UnexpectedValueException('Type mismatch in return from handler.  Handler must returned processed $args.');
                }
            }
            $event->saveResults($args);
        }
        return $event;
    }

    /**
     * Chain load all handlers to process passed args feeding response from one
     * to the next
     *
     * @param ZEvent $event
     * @param mixed $args
     *
     * @return ZEvent $event
     */
    public function processUntil(ZEvent $event, $args)
    {
        $name = $event->getName();
        if ($this->existsHandler($name)) {
            $sourceType = gettype($args);
            foreach ($this->handlers[$name] as $handler) {
                $result = $this->invoke($handler, $event, $args);
                if ($result) {
                    // validate return
                    if (gettype($result) == $sourceType) {
                        $args = $result;
                        $event->flagNotified();
                        break;
                    } else {
                        throw new UnexpectedValueException('Type mismatch in return from handler.  Handler must returned processed $args');
                    }
                }
            }
            $event->saveResults($args);
        }
        return $event;
    }

    /**
     * Invoke handler
     *
     * @param callback $handler
     * @param ZEvent $event
     * @param mixed $args
     *
     * return bool
     */
    private function invoke($handler, $event, $args = null)
    {
        if (is_null($args)) {
            return call_user_func($handler, $event);
        }
        return call_user_func_array($handler, array($event, $args));
    }

    /**
     * Return true of event of $name exists
     * @param string $name
     * @return bool
     */
    public function existsHandler($name)
    {
        return (isset($this->handlers[$name]) && count($this->handlers[$name] > 0) ? true : false);
    }
}



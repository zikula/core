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
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Zikula_Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class Zikula_Event extends GenericEvent
{
    /**
     * Storage for any process type events.
     *
     * @var mixed
     */
    public $data;

    /**
     * Exception.
     *
     * @var Exception
     */
    protected $exception;

    /**
     * Encapsulate an event called with $subject.
     *
     * @param mixed  $subject Usually and object or other PHP callable.
     * @param array  $args    Arguments to store in the event.
     * @param mixed  $data    Convenience argument of data for optional processing.
     *
     * @throws InvalidArgumentException When name is empty.
     */
    public function __construct($subject = null, $args = null, $data = null)
    {
        $funcArgs = func_get_args();
        if (isset($funcArgs[0]) && is_string($funcArgs[0])) {
            $this->setName($funcArgs[0]);
            $subject = isset($funcArgs[1]) ? $funcArgs[1]: null;
            $args = isset($funcArgs[2]) ? $funcArgs[2]: null;
            $data = isset($funcArgs[3]) ? $funcArgs[3]: null;
        }

        $args = null !== $args ? $args: array();
        $this->data = $data;
        parent::__construct($subject, $args);
    }

    /**
     * Signal to stop further event notification.
     *
     * @deprecated since 1.4
     * @use Symfony\Component\EventDispatcher\GenericEvent::stopPropagation()
     *
     * @return void
     */
    public function stop()
    {
        $this->stopPropagation();
    }

    /**
     * Has the event been stopped.
     *
     * @deprecated since 1.4
     * @use Symfony\Component\EventDispatcher\GenericEvent::isPropagationStopped()
     *
     * @return boolean
     */
    public function isStopped()
    {
        return $this->isPropagationStopped();
    }

    /**
     * Set data.
     *
     * @param mixed $data Data to be saved.
     *
     * @return Zikula_Event
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Add argument to event.
     *
     * @param string $key   Argument name.
     * @param mixed  $value Value.
     *
     * @deprecated since 1.4
     * @use Symfony\Component\EventDispatcher\GenericEvent::setArgument()
     *
     * @return Zikula_Event
     */
    public function setArg($key, $value)
    {
        return $this->setArgument($key, $value);
    }

    /**
     * Set args property.
     *
     * @param array $args Arguments.
     *
     * @deprecated since 1.4
     * @use Symfony\Component\EventDispatcher\GenericEvent::setArguments()
     *
     * @return Zikula_Event
     */
    public function setArgs(array $args = array())
    {
        return $this->setArguments($args);
    }

    /**
     * Get argument by key.
     *
     * @param string $key Key.
     *
     * @deprecated since 1.4
     * @use Symfony\Component\EventDispatcher\GenericEvent::getArgument()
     *
     * @throws InvalidArgumentException If key is not found.
     *
     * @return mixed Contents of array key.
     */
    public function getArg($key)
    {
        return $this->getArgument($key);
    }

    /**
     * Getter for all arguments.
     *
     * @deprecated since 1.4
     * @use Symfony\Component\EventDispatcher\GenericEvent::getArguments()
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->getArguments();
    }

    /**
     * Getter for Data property.
     *
     * @return mixed Data property.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Has argument.
     *
     * @param string $key Key of arguments array.
     *
     * @deprecated since 1.4
     * @use Symfony\Component\EventDispatcher\GenericEvent::hasArgument()
     *
     * @return boolean
     */
    public function hasArg($key)
    {
        return $this->hasArgument($key);
    }

    /**
     * Get exception.
     *
     * @throws RuntimeException If no exeception was set.
     *
     * @return Exception
     */
    public function getException()
    {
        if (!$this->hasException()) {
            throw new RuntimeException('No exception was set during this event notification.');
        }

        return $this->exception;
    }

    /**
     * Set exception.
     *
     * Rather than throw an exception within an event handler,
     * instead you can store it here then stop() execution.
     * This can then be rethrown or handled politely.
     *
     * @param Exception $exception Exception.
     *
     * @return void
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * Has exception.
     *
     * @return Exception
     */
    public function hasException()
    {
        return (bool)$this->exception;
    }

    /**
     * Sets the EventManager property.
     *
     * @param EventDispatcherInterface $eventManager
     *
     * @deprecated since 1.4
     * @use Symfony\Component\EventDispatcher\GenericEvent::setDispatcher()
     *
     * @return void
     */
    public function setEventManager(EventDispatcherInterface $eventManager)
    {
        $this->setDispatcher($eventManager);
    }

    /**
     * Gets the EventManager.
     *
     * @deprecated since 1.4
     * @use Symfony\Component\EventDispatcher\GenericEvent::getDispatcher()
     *
     * @return Zikula_EventManager
     */
    public function getEventManager()
    {
        return $this->getDispatcher();
    }
}

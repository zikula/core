<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Zikula_Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 * @deprecated
 */
class Zikula_Event extends GenericEvent
{
    /**
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
     * @param string $name    Event name
     * @param mixed  $subject Usually and object or other PHP callable
     * @param array  $args    Arguments to store in the event
     * @param mixed  $data    Convenience argument of data for optional processing
     */
    public function __construct($name, $subject = null, array $args = [], $data = null)
    {
        $this->setName($name);
        $this->data = $data;

        parent::__construct($subject, $args);
    }

    /**
     * Signal to stop further event notification.
     *
     * @deprecated since 1.4.0
     * @see Symfony\Component\EventDispatcher\GenericEvent::stopPropagation()
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
     * @deprecated since 1.4.0
     * @see Symfony\Component\EventDispatcher\GenericEvent::isPropagationStopped()
     *
     * @return boolean
     */
    public function isStopped()
    {
        return $this->isPropagationStopped();
    }

    /**
     * Add argument to event.
     *
     * @param string $key   Argument name
     * @param mixed  $value Value
     *
     * @deprecated since 1.4.0
     * @see Symfony\Component\EventDispatcher\GenericEvent::setArgument()
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
     * @param array $args Arguments
     *
     * @deprecated since 1.4.0
     * @see Symfony\Component\EventDispatcher\GenericEvent::setArguments()
     *
     * @return Zikula_Event
     */
    public function setArgs(array $args = [])
    {
        return $this->setArguments($args);
    }

    /**
     * Get argument by key.
     *
     * @param string $key Key
     *
     * @deprecated since 1.4.0
     * @see Symfony\Component\EventDispatcher\GenericEvent::getArgument()
     *
     * @throws InvalidArgumentException If key is not found
     *
     * @return mixed Contents of array key
     */
    public function getArg($key)
    {
        return $this->getArgument($key);
    }

    /**
     * Sets the data
     *
     * @param $data
     *
     * @return Zikula_Event
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Getter for all arguments.
     *
     * @deprecated since 1.4.0
     * @see Symfony\Component\EventDispatcher\GenericEvent::getArguments()
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
     * @return mixed Data property
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Has argument.
     *
     * @param string $key Key of arguments array
     *
     * @deprecated since 1.4.0
     * @see Symfony\Component\EventDispatcher\GenericEvent::hasArgument()
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
     * @throws RuntimeException If no exeception was set
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
     * @param Exception $exception Exception
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
     * @deprecated since 1.4.0
     * @see Symfony\Component\EventDispatcher\GenericEvent::setDispatcher()
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
     * @deprecated since 1.4.0
     * @see Symfony\Component\EventDispatcher\GenericEvent::getDispatcher()
     *
     * @return Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
     */
    public function getEventManager()
    {
        return $this->getDispatcher();
    }
}

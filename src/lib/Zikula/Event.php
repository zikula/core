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
 * Zikula_Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class Zikula_Event implements Zikula_EventInterface, ArrayAccess
{
    /**
     * Name of the event.
     *
     * @var string
     */
    protected $name;

    /**
     * Observer pattern subject.
     *
     * @var mixed usually object or callable
     */
    protected $subject;

    /**
     * Array of arguments.
     *
     * @var array
     */
    protected $args;

    /**
     * Signal to stop further notification.
     *
     * @var boolean
     */
    protected $stop = false;

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
     * EventManager instance.
     *
     * @var Zikula_EventManagerInterface
     */
    protected $eventManager;

    /**
     * Encapsulate an event called $name with $subject.
     *
     * @param string $name    Name of the event.
     * @param mixed  $subject Usually and object or other PHP callable.
     * @param array  $args    Arguments to store in the event.
     * @param mixed  $data    Convenience argument of data for optional processing.
     *
     * @throws InvalidArgumentException When name is empty.
     */
    public function __construct($name, $subject = null, array $args = array(), $data = null)
    {
        // must have a name
        if (empty($name)) {
            throw new InvalidArgumentException('Event name cannot be empty');
        }

        $this->name = $name;
        $this->subject = $subject;
        $this->args = $args;
        $this->data = $data;
    }

    /**
     * Signal to stop further event notification.
     *
     * @return void
     */
    public function stop()
    {
        $this->stop = true;
    }

    /**
     * Has the event been stopped.
     *
     * @return boolean
     */
    public function isStopped()
    {
        return $this->stop;
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
     * @return Zikula_Event
     */
    public function setArg($key, $value)
    {
        $this->args[$key] = $value;

        return $this;
    }

    /**
     * Set args property.
     *
     * @param array $args Arguments.
     *
     * @return Zikula_Event
     */
    public function setArgs(array $args = array())
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Get argument by key.
     *
     * @param string $key Key.
     *
     * @throws InvalidArgumentException If key is not found.
     *
     * @return mixed Contents of array key.
     */
    public function getArg($key)
    {
        if ($this->hasArg($key)) {
            return $this->args[$key];
        }

        throw new InvalidArgumentException(sprintf('%s not found in %s', $key, $this->name));
    }

    /**
     * Getter for all arguments.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Getter for subject property.
     *
     * @return mixed $subject The observer subject.
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get event name.
     *
     * @return string Name property.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set event name.
     *
     * @param type $name Event Name.
     *
     * @return Zikula_Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return boolean
     */
    public function hasArg($key)
    {
        return array_key_exists($key, $this->args);
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
     * @param Zikula_EventManagerInterface $eventManager
     *
     * @return void
     */
    public function setEventManager(Zikula_EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Gets the EventManager.
     *
     * @return Zikula_EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * ArrayAccess for argument getter.
     *
     * @param string $key Array key.
     *
     * @throws InvalidArgumentException If key does not exist in $this->args.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        if ($this->hasArg($key)) {
            return $this->args[$key];
        }

        throw new InvalidArgumentException(sprintf('The requested key %s does not exist', $key));
    }

    /**
     * ArrayAccess for argument setter.
     *
     * @param string $key   Array key to set.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->setArg($key, $value);
    }

    /**
     * ArrayAccess for unset argument.
     *
     * @param string $key Array key.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        if ($this->hasArg($key)) {
            unset($this->args[$key]);
        }
    }

    /**
     * AccessArray has argument.
     *
     * @param string $key Array key.
     *
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->hasArg($key);
    }

}

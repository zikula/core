<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class Event implements ArrayAccess
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
     * Flag when notified.
     *
     * @var boolean
     */
    protected $notified;

    /**
     * Storage for any process type events.
     *
     * @var mixed
     */
    public $data;

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
        if (empty($name) || !is_string($name)) {
            throw new InvalidArgumentException('$name is a required argument and must be a string.');
        }

        $this->name = $name;
        $this->subject = $subject;
        $this->args = $args;
        $this->data = $data;
        $this->notified = false;
    }

    /**
     * Mark event as completed.
     *
     * @return void
     */
    public function setNotified()
    {
        $this->notified = true;
    }

    /**
     * Set data.
     *
     * @param mixed $data Data to be saved.
     *
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Add argument to event.
     *
     * @param string $key   Argument name.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function setArg($key, $value)
    {
        $this->args[$key] = $value;
    }

    /**
     * Return value from $this->args[$key].
     *
     * @param string $key Key to the args array.
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
     * Getter for args property.
     *
     * @return array Args property.
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
     * Getter for name property.
     *
     * @return string Name property.
     */
    public function getName()
    {
        return $this->name;
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
     * Answer question, "Has the event completed?".
     *
     * @return bool if handler has executed.
     */
    public function hasNotified()
    {
        return $this->notified;
    }

    /**
     * Check if $key exists in args.
     *
     * @param string $key Key of args array.
     *
     * @return boolean
     */
    public function hasArg($key)
    {
        return array_key_exists($key, $this->args);
    }

    // implement ArrayAccess on $this->args property.

    /**
     * ArrayAccess for $this->getArg()
     *
     * @param string $key Array key.
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
     * ArrayAccess for $this->setArg($key, $value).
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
     * ArrayAccess for unset($key).
     *
     * @param string $key
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
     * AccessArray for $this->hasArg($key).
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
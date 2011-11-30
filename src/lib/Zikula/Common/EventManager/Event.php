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

/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class Event implements EventInterface
{
    /**
     * Name of the event.
     *
     * @var string
     */
    protected $name;

    /**
     * Signal to stop further notification.
     *
     * @var boolean
     */
    protected $stop = false;

    /**
     * EventManager instance.
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

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
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * @throws \InvalidArgumentException If key is not found.
     *
     * @return mixed Contents of array key.
     */
    public function getArg($key)
    {
        if ($this->hasArg($key)) {
            return $this->args[$key];
        }

        throw new \InvalidArgumentException(sprintf('%s not found in %s', $key, $this->name));
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
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets the EventManager property.
     *
     * @param EventManagerInterface $eventManager
     *
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Gets the EventManager.
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }
}
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
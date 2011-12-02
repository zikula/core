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
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class Event extends \Symfony\Component\EventDispatcher\Event
{
    /**
     * EventManager instance.
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Signal to stop further event notification.
     *
     * @deprecated
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
     * @deprecated
     *
     * @return boolean
     */
    public function isStopped()
    {
        return $this->isPropagationStopped();
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
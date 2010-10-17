<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Custom Event Handler interface.
 *
 * EventHandlers that implement this class should implement an indexed array
 * of eventname => handlerMethod like the following.  (Can contain multiple
 * index pairs).
 *
 * protected $eventNames = array('name' => 'handlerMethod')
 *
 * The handler methods must be implemented as followes:
 *
 * public function handler(Event $event)
 */
abstract class Zikula_EventHandler
{
    /**
     * Event names.
     *
     * @var array
     */
    protected $eventNames;

    /**
     * EventManager instance.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * ServiceManager instance.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     *
     * @throws InvalidArgumentException If $this->eventNames is invalid.
     */
    public function __construct(Zikula_ServiceManager $serviceManager)
    {
        if (!is_array($this->eventNames) || !$this->eventNames) {
            throw new InvalidArgumentException(sprintf("%s->eventNames property contain indexed array of 'eventname' => handlerMethod", get_class($this)));
        }

        $this->serviceManager = $serviceManager;
        $this->eventManager = $this->serviceManager->getService('zikula.eventmanager');
    }

    /**
     * Get event names.
     *
     * @return array
     */
    public function getEventNames()
    {
        return $this->eventNames;
    }

    /**
     * Get eventManager.
     *
     * @return Zikula_EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Get servicemanager.
     *
     * @return Zikula_ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Attach handler with EventManager.
     *
     * @throws InvalidArgumentException If $this->eventNames data is invalid.
     *
     * @return void
     */
    public function attach()
    {
        if (!is_array($this->eventNames)) {
            throw new InvalidArgumentException(sprintf("%s->eventNames property contain indexed array of 'eventname' => handlerMethod", get_class($this)));
        }

        foreach ($this->eventNames as $name => $method) {
            if (is_integer($name)) {
                throw new InvalidArgumentException(sprintf("%s->eventNames property contain indexed array of 'eventname' => handlerMethod", get_class($this)));
            }

            $this->eventManager->attach($name, array($this, $method));
        }
    }

    /**
     * Detach event from EventManager.
     *
     * @return void
     */
    public function detach()
    {
        foreach ($this->eventNames as $name => $method) {
            $this->eventManager->detach($name, array($this, $method));
        }
    }

    /**
     * Post constructor hook.
     *
     * @return void
     */
    public function setup()
    {
    }
}
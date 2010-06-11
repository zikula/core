<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
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
    protected $eventManager;
    protected $serviceManager;

    /**
     * Constructor validation.
     */
    public function __construct(Zikula_EventManager $eventManager, Zikula_ServiceManager $serviceManager)
    {
        if (!is_array($this->eventNames) || !$this->eventNames) {
            throw new InvalidArgumentException(sprintf("%s->eventNames property contain indexed array of 'eventname' => handlerMethod", get_class($this)));
        }
        
        $this->eventManager = $eventManager;
        $this->serviceManager = $serviceManager;
    }

    /**
     * Attach handler with EventManager.
     */
    public function attach()
    {
        foreach ($this->eventNames as $name => $method) {
            if (is_integer($name)) {
                throw new InvalidArgumentException(sprintf("%s->eventNames property contain indexed array of 'eventname' => handlerMethod", get_class($this)));
            }

            EventUtil::attach($name, array($this, $method));
        }
    }

    public function detach()
    {
        foreach ($this->eventNames as $name => $method) {
            EventUtil::detach($name, array($this, $method));
        }
    }
}
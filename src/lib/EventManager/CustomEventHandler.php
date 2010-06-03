<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package EventManager
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
 * protected $names = array('name' => 'handlerMethod')
 *
 * The handler methods must be implemented as followes:
 *
 * public function handler(Event $event)
 */
abstract class CustomEventHandler
{
    /**
     * Constructor validation.
     */
    public function __construct()
    {
        if (!is_array($this->names) || !$this->names) {
            throw new InvalidArgumentException(sprintf('%s->names property contain indexed array of eventname => handlerMethod', get_class($this)));
        }
    }

    /**
     * Attach handler with EventManager.
     */
    public function attach()
    {
        foreach ($this->names as $name => $method) {
            if (is_integer($name)) {
                throw new InvalidArgumentException(sprintf('%s->names property contain indexed array of eventname => handlerMethod', get_class($this)));
            }

            EventManagerUtil::attach($name, array($this, $method));
        }
    }
}
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
 */
abstract class CustomEventHandler
{
    /**
     * Constructor validation.
     */
    public function __construct()
    {
        if (is_null($this->names)) {
            throw new InvalidArgumentException(sprintf('$names property must be defined in %s', get_class($this)));
        }

        if (!is_array($this->names)) {
            throw new InvalidArgumentException(sprintf('$names property must be an array in %s', get_class($this)));
        }

        if (!$this->names) {
            throw new InvalidArgumentException(sprintf('$names property contain at least one array element in %s', get_class($this)));
        }
    }

    /**
     * Attach handler with EventManager.
     */
    public function attach()
    {
        foreach ($this->names as $name) {
            EventManagerUtil::attach($name, array($this, 'handler'));
        }
    }

    /**
     * Child must implment this interface.
     */
    abstract function handler(Event $event);
}
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
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class Zikula_Event extends Zikula\Core\Event\GenericEvent
{
    /**
     * Encapsulate an event called $name with $subject.
     *
     * @param string $name    Name of the event.
     * @param mixed  $subject Usually and object or other PHP callable.
     * @param array  $args    Arguments to store in the event.
     * @param mixed  $data    Convenience argument of data for optional processing.
     *
     * @throws \InvalidArgumentException When name is empty.
     */
    public function __construct($name, $subject = null, array $args = array(), $data = null)
    {
        // must have a name
        if (empty($name)) {
            throw new \InvalidArgumentException('Event name cannot be empty');
        }

        $this->setName($name);
        $this->subject = $subject;
        $this->args = $args;
        $this->data = $data;
    }
}
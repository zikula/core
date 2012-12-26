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

namespace Zikula\Core\Event;

use Symfony\Component\EventDispatcher\GenericEvent as Event;

/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class GenericEvent extends Event
{
    /**
     * Storage for any process type events.
     *
     * @var mixed
     */
    public $data;

    /**
     * Encapsulate an event with $subject, $args, and $data.
     *
     * @param mixed  $subject Usually an object or other PHP callable.
     * @param array  $args    Arguments to store in the event.
     * @param mixed  $data    Convenience argument of data for optional processing.
     */
    public function __construct($subject = null, array $args = array(), $data = null)
    {
        $this->data = $data;

        parent::__construct($subject, $args);
    }

    /**
     * Set data.
     *
     * @param mixed $data Data to be saved.
     *
     * @return GenericEvent
     */
    public function setData($data)
    {
        $this->data = $data;

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
}
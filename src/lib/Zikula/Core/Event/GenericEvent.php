<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Event;

/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 */
class GenericEvent extends \Zikula_Event
{
    /**
     * Encapsulate an event with $subject, $args, and $data.
     *
     * @param mixed  $subject Usually an object or other PHP callable.
     * @param array  $args    Arguments to store in the event.
     * @param mixed  $data    Convenience argument of data for optional processing.
     */
    public function __construct($subject = null, array $args = [], $data = null)
    {
        parent::__construct(null, $subject, $args, $data);
    }
}

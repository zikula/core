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

use Symfony\Component\EventDispatcher\GenericEvent as SymfonyGenericEvent;

/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 */
class GenericEvent extends SymfonyGenericEvent
{
    /**
     * @var mixed
     */
    public $data;

    /**
     * Exception.
     *
     * @var \Exception
     */
    protected $exception;

    /**
     * Encapsulate an event with $subject, $args, and $data.
     *
     * @param mixed  $subject Usually an object or other PHP callable
     * @param array  $args    Arguments to store in the event
     * @param mixed  $data    Convenience argument of data for optional processing
     */
    public function __construct($subject = null, array $args = [], $data = null)
    {
        $this->data = $data;
        parent::__construct($subject, $args);
    }

    /**
     * Sets the data
     *
     * @param $data
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
     * @return mixed Data property
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get exception.
     *
     * @throws \RuntimeException If no exeception was set
     *
     * @return \Exception
     */
    public function getException()
    {
        if (!$this->hasException()) {
            throw new \RuntimeException('No exception was set during this event notification.');
        }

        return $this->exception;
    }

    /**
     * Set exception.
     *
     * Rather than throw an exception within an event handler,
     * instead you can store it here then stop() execution.
     * This can then be rethrown or handled politely.
     *
     * @param \Exception $exception Exception
     *
     * @return void
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * Has exception.
     *
     * @return bool
     */
    public function hasException()
    {
        return (bool)$this->exception;
    }
}

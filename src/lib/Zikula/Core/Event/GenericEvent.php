<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Event;

use Exception;
use RuntimeException;
use Symfony\Contracts\EventDispatcher\Event as SymfonyGenericEvent;

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
     * @var Exception
     */
    protected $exception;

    /**
     * Encapsulate an event with $subject, $args, and $data.
     *
     * @param mixed $subject Usually an object or other PHP callable
     * @param array $args Arguments to store in the event
     * @param mixed $data Convenience argument of data for optional processing
     */
    public function __construct($subject = null, array $args = [], $data = null)
    {
        $this->data = $data;
        parent::__construct($subject, $args);
    }

    /**
     * Sets the data
     *
     * @param mixed $data
     */
    public function setData($data): self
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
     * @throws RuntimeException If no exception was set
     */
    public function getException(): Exception
    {
        if (!$this->hasException()) {
            throw new RuntimeException('No exception was set during this event notification.');
        }

        return $this->exception;
    }

    /**
     * Set exception.
     *
     * Rather than throw an exception within an event handler,
     * instead you can store it here then stop() execution.
     * This can then be rethrown or handled politely.
     */
    public function setException(Exception $exception): void
    {
        $this->exception = $exception;
    }

    public function hasException(): bool
    {
        return (bool)$this->exception;
    }
}

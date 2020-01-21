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

namespace Zikula\Bundle\CoreBundle\Event;

use Exception;
use RuntimeException;
use Symfony\Contracts\EventDispatcher\Event as SymfonyGenericEvent;

/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 */
class GenericEvent extends SymfonyGenericEvent implements \ArrayAccess, \IteratorAggregate
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
     * @var mixed
     */
    protected $subject;

    /**
     * @var array
     */
    protected $args = [];

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
        $this->subject = $subject;
        $this->args = $args;
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

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    public function getArguments(): array
    {
        return $this->args;
    }

    public function setArguments(array $args): void
    {
        $this->args = $args;
    }

    public function setArgument(string $key, $val)
    {
        $this->args[$key] = $val;
    }

    public function hasArgument($key)
    {
        return \array_key_exists($key, $this->args);
    }

    public function getArgument(string $key)
    {
        return $this->args[$key];
    }

    public function offsetExists($offset)
    {
        return $this->hasArgument($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getArgument($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setArgument($offset, $value);
    }

    public function offsetUnset($offset)
    {
        if ($this->hasArgument($offset)) {
            unset($this->args[$offset]);
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->args);
    }
}

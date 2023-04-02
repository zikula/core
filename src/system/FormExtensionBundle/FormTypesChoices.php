<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\FormExtensionBundle;

use ArrayAccess;
use Exception;
use Iterator;

class FormTypesChoices implements ArrayAccess, Iterator
{
    private array $choices;

    public function __construct(array $choices = [])
    {
        $this->choices = $choices;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->choices[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->choices[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->choices[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('Not allowed to unset!');
    }

    public function rewind(): void
    {
        reset($this->choices);
    }

    public function current(): mixed
    {
        return current($this->choices);
    }

    public function key(): mixed
    {
        return key($this->choices);
    }

    public function next(): void
    {
        next($this->choices);
    }

    public function valid(): bool
    {
        return null !== key($this->choices);
    }
}

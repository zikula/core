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

namespace Zikula\Bundle\FormExtensionBundle;

use ArrayAccess;
use Exception;
use Iterator;

class FormTypesChoices implements ArrayAccess, Iterator
{
    /**
     * @var array
     */
    private $choices;

    public function __construct(array $choices = [])
    {
        $this->choices = $choices;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->choices[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->choices[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->choices[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        throw new Exception('Not allowed to unset!');
    }

    public function rewind()
    {
        return reset($this->choices);
    }

    public function current()
    {
        return current($this->choices);
    }

    public function key()
    {
        return key($this->choices);
    }

    public function next()
    {
        return next($this->choices);
    }

    public function valid(): bool
    {
        return null !== key($this->choices);
    }
}

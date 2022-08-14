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

namespace Zikula\Bundle\CoreBundle\Collection;

use ArrayObject;
use InvalidArgumentException;
use Traversable;

/**
 * Generic Collection.
 */
class Container implements CollectionInterface
{
    protected ArrayObject $collection;

    public function __construct(private readonly string $name, ArrayObject $collection = null)
    {
        $this->collection = $collection ?? new ArrayObject([]);
    }

    public function getCollection(): ArrayObject
    {
        return $this->collection;
    }

    public function setCollection(ArrayObject $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function add(mixed $value): self
    {
        $this->collection[] = $value;

        return $this;
    }

    public function get(mixed $key): mixed
    {
        return $this->collection[$key];
    }

    public function set(mixed $key, mixed $value): self
    {
        $this->collection[$key] = $value;

        return $this;
    }

    public function has(mixed $key): bool
    {
        return $this->offsetExists($key);
    }

    public function del(mixed $key): self
    {
        if ($this->has($key)) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    // IteratorAggregate interface implementation

    /**
     * Retrieve an external iterator ({@see IteratorAggregate}).
     */
    public function getIterator(): Traversable
    {
        return $this->collection->getIterator();
    }

    // countable interface implementation

    /**
     * Count the number of elements in the collection.
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Indicates whether this collection is initialized or not.
     */
    public function hasCollection(): bool
    {
        return !$this->collection;
    }

    // ArrayAccess interface implementation

    /**
     * Returns the value at the specified offset (see {@link ArrayAccess::offsetGet()}).
     *
     * @throws InvalidArgumentException Thrown if the key does not exist in the collection
     */
    public function offsetGet(mixed $key): mixed
    {
        if ($this->has($key)) {
            return $this->collection[$key];
        }
        throw new InvalidArgumentException(sprintf('Key %s does not exist in collection', $key));
    }

    public function offsetSet(mixed $key, mixed $value): self
    {
        $this->collection[$key] = $value;

        return $this;
    }

    /**
     * Indicate whether the specified offset is set (see {@link ArrayAccess::offsetExists()}).
     */
    public function offsetExists(mixed $key): bool
    {
        return $this->collection->offsetExists($key);
    }

    /**
     * Unset the specified offset (see {@link ArrayAccess::offsetUnset()}).
     */
    public function offsetUnset(mixed $key): self
    {
        $this->collection->offsetUnset($key);

        return $this;
    }
}

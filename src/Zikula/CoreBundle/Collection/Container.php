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

namespace Zikula\Bundle\CoreBundle\Collection;

use ArrayObject;
use InvalidArgumentException;
use Traversable;

/**
 * Generic Collection.
 */
class Container implements CollectionInterface
{
    /**
     * The name of the collection.
     *
     * @var string
     */
    protected $name;

    /**
     * Collection.
     *
     * @var ArrayObject
     */
    protected $collection;

    public function __construct(string $name, ArrayObject $collection = null)
    {
        $this->name = $name;
        $this->collection = !$collection ? new ArrayObject([]) : $collection;
    }

    /**
     * Retrieve the collection.
     */
    public function getCollection(): ArrayObject
    {
        return $this->collection;
    }

    /**
     * Set the collection.
     */
    public function setCollection(ArrayObject $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * Retrieve the name of the collection.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Append a value to the collection without an index.
     *
     * @param mixed $value The value to append
     */
    public function add($value): void
    {
        $this->collection[] = $value;
    }

    /**
     * Set the value of the specified item in the collection.
     *
     * @param mixed $key The index of the item for which the value should be set
     * @param mixed $value The value of the item
     */
    public function set($key, $value): void
    {
        $this->collection[$key] = $value;
    }

    /**
     * Retrieve the specified item from the collection.
     *
     * @param mixed $key The index of the item to retrieve
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->collection[$key];
    }

    /**
     * Indicates whether the element indexed by the $key is set.
     *
     * @param mixed $key The index to check
     */
    public function has($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Remove the specified item from the collection.
     *
     * @param mixed $key The index of the item to remove
     */
    public function del($key): void
    {
        if ($this->has($key)) {
            $this->offsetUnset($key);
        }
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
     * @param mixed $key The offset to retrieve
     *
     * @return mixed The value at the specified offset
     *
     * @throws InvalidArgumentException Thrown if the key does not exist in the collection
     */
    public function offsetGet($key)
    {
        if ($this->has($key)) {
            return $this->collection[$key];
        }
        throw new InvalidArgumentException(sprintf('Key %s does not exist in collection', $key));
    }

    /**
     * Set the value at the specified offset (see {@link ArrayAccess::offsetSet()}).
     *
     * @param mixed $key The offset to retrieve
     * @param mixed $value The value to set at the specified offset
     */
    public function offsetSet($key, $value): void
    {
        $this->collection[$key] = $value;
    }

    /**
     * Indicate whether the specified offset is set (see {@link ArrayAccess::offsetExists()}).
     *
     * @param mixed $key The offset to check
     */
    public function offsetExists($key): bool
    {
        return $this->collection->offsetExists($key);
    }

    /**
     * Unset the specified offset (see {@link ArrayAccess::offsetUnset()}).
     *
     * @param mixed $key The offset to unset
     */
    public function offsetUnset($key): void
    {
        $this->collection->offsetUnset($key);
    }
}

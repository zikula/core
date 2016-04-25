<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Common\Collection\CollectionInterface;

/**
 * Generic Collection.
 */
class Zikula_Collection_Container implements CollectionInterface
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

    /**
     * Construct a new Zikula_Collection.
     *
     * @param string      $name       The name of the collection.
     * @param ArrayObject $collection The collection (optional).
     */
    public function __construct($name, ArrayObject $collection = null)
    {
        $this->name = $name;
        $this->collection = !$collection ? new ArrayObject(array()) : $collection;
    }

    /**
     * Retrieve the collection.
     *
     * @return ArrayObject The collection.
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set the collection.
     *
     * @param ArrayObject $collection The collection.
     *
     * @return void
     */
    public function setCollection(ArrayObject $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Retrieve the name of the collection.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Append a value to the collection without an index.
     *
     * @param mixed $value The value to append.
     *
     * @return void
     */
    public function add($value)
    {
        $this->collection[] = $value;
    }

    /**
     * Set the value of the specified item in the collection.
     *
     * @param mixed $key   The index of the item for which the value should be set.
     * @param mixed $value The value of the item.
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->collection[$key] = $value;
    }

    /**
     * Retrieve the specified item from the collection.
     *
     * @param mixed $key The index of the item to retrieve.
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
     * @param mixed $key The index to check.
     *
     * @return boolean True if the collection contains the item identified by $key; otherwise false.
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Remove the specified item from the collection.
     *
     * @param mixed $key The index of the item to remove.
     *
     * @return void
     */
    public function del($key)
    {
        if ($this->has($key)) {
            $this->offsetUnset($key);
        }
    }

    // iteratoraggregate interface implementation

    /**
     * Retrieve an external iterator (see {@link IteratorAggregate}).
     *
     * @return Traversable The iterator instance.
     */
    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    // countable interface implementation

    /**
     * Count the number of elements in the collection.
     *
     * @return integer The number of elements in the collection.
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Indicates whether this collection is initialized or not.
     *
     * @return boolean True if the instance has a collection, otherwise false.
     */
    public function hasCollection()
    {
        return !$this->collection;
    }

    // ArrayAccess interface implementation

    /**
     * Returns the value at the specified offset (see {@link ArrayAccess::offsetGet()}).
     *
     * @param mixed $key The offset to retrieve.
     *
     * @return mixed The value at the specified offset.
     *
     * @throws InvalidArgumentException Thrown if the key does not exist in the collection.
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
     * @param mixed $key   The offset to retrieve.
     * @param mixed $value The value to set at the specified offset.
     *
     * @return mixed
     */
    public function offsetSet($key, $value)
    {
        $this->collection[$key] = $value;
    }

    /**
     * Indicate whether the specified offset is set (see {@link ArrayAccess::offsetExists()}).
     *
     * @param mixed $key The offset to check.
     *
     * @return boolean True if the offset is set, otherwise false.
     */
    public function offsetExists($key)
    {
        return $this->collection->offsetExists($key);
    }

    /**
     * Unset the specified offset (see {@link ArrayAccess::offsetUnset()}).
     *
     * @param mixed $key The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->collection->offsetUnset($key);
    }
}

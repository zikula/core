<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Collection
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Generic Collection.
 */
class Zikula_Collection_Container implements Zikula_Collection_Interface
{
    /**
     * Name.
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

    public function __construct($name, ArrayObject $collection = null)
    {
        $this->name = $name;
        $this->collection = !$collection ? new ArrayObject(array()) : $collection;
    }

    /**
     * Get collection.
     *
     * @return ArrayObject
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set collection.
     * 
     * @param ArrayObject $collection The collection
     *
     * @return void
     */
    public function setCollection(ArrayObject $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add without index.
     *
     * @param mixed $value Value.
     *
     * @return void
     */
    public function add($value)
    {
        $this->collection[] = $value;
    }

    /**
     * Set with index.
     *
     * @param string $key   Key.
     * @param mixed  $value Value.
     */
    public function set($key, $value)
    {
        $this->collection[$key] = $value;
    }

    /**
     * Get element by key.
     *
     * @param string $key Key.
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->collection[$key];
    }

    /**
     * Offset exists.
     *
     * @param string $key Key
     *
     * @return boolean
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset offset.
     *
     * @param string $key Key.
     *
     * @return void
     */
    public function del($key)
    {
        if ($this->has($key)) {
            $this->offsetUnset($key);
        }
    }

    // interatoraggregate interface implementation

    /**
     * Get iterator for collection.
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    // countable interface implementation

    /**
     * Number of elements in collection.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Has collection.
     *
     * @return boolean
     */
    public function hasCollection()
    {
        return !$this->collection;
    }

    // ArrayAccess interface implementation

    /**
     * Offset Get for ArrayAccess.
     *
     * @param string $key Key.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        if ($this->has($key)) {
            return $this->collection[$key];
        }
        throw new InvalidArgumentException(sprintf('Key %s does not exist in collection', $key));
    }

    /**
     * Offset Set for ArrayAccess.
     *
     * @param string $key   Key.
     * @param mixed  $value Value.
     *
     * @return mixed
     */
    public function offsetSet($key, $value)
    {
        $this->collection[$key] = $value;
    }

    /**
     * Isset implementation for key.
     *
     * @param string $key Key.
     *
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->collection->offsetExists($key);
    }

    /**
     * Unset key.
     *
     * @param <type> $key Key.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->collection->offsetUnset($key);
    }
}

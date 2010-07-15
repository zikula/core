<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Zikula_Collection_Container implements Zikula_Collection_Interface
{
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

    public function getCollection()
    {
        return $this->collection;
    }

    public function setCollection(ArrayObject $collection)
    {
        $this->collection = $collection;
    }

    public function getName()
    {
        return $this->name;
    }

    public function add($value)
    {
        $this->collection[] = $value;
    }

    public function set($key, $value)
    {
        $this->collection[$key] = $value;
    }

    public function get($key)
    {
        $this->collection[$key];
    }

    public function has($key)
    {
        return $this->offsetExists($key);
    }

    public function del($key)
    {
        if ($this->has($key)) {
            $this->offsetUnset($key);
        }
    }

    // interatoraggregate interface implementation

    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    // countable interface implementation
    public function count()
    {
        return count($this->collection);
    }

    public function hasCollection()
    {
        return !$this->collection;
    }

    // ArrayAccess interface implementation

    public function offsetGet($key)
    {
        if ($this->has($key)) {
            return $this->collection[$key];
        }
        throw new InvalidArgumentException(sprintf('Key %s does not exist in collection', $key));
    }

    public function offsetSet($key, $value)
    {
        $this->collection[$key] = $value;
    }

    public function offsetExists($key)
    {
        return $this->collection->offsetExists($key);
    }

    public function offsetUnset($key)
    {
        return $this->collection->offsetUnset($key);
    }
}

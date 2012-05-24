<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Request
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Request collection container.
 */
class Zikula_Request_Collection implements ArrayAccess
{
    /**
     * Collection.
     *
     * @var array
     */
    protected $collection;

    /**
     * Constructor.
     *
     * @param array $collection Array for collection.
     */
    public function __construct($collection = array())
    {
        if (is_null($collection)) {
            $collection = array();
        }
        $this->collection = $collection;
    }

    /**
     * Get collection.
     *
     * @return array
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set collection.
     *
     * @param array $collection Collection to set.
     *
     * @return void
     */
    public function setCollection(array $collection)
    {
        $this->collection = $collection;
    }


    /**
     * Get.
     *
     * @param string $key     Key.
     * @param mixed  $default Default value if not set.
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return ($this->has($key)) ? $this->collection[$key] : $default;
    }

    /**
     * Set.
     *
     * @param string $key   Key.
     * @param mixed  $value Value to set.
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->collection[$key] = $value;
    }

    /**
     * Has.
     *
     * @param string $key Key.
     *
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists($key, $this->collection);
    }

    /**
     * Filter key.
     *
     * @param string  $key     Key.
     * @param mixed   $default Default = null.
     * @param integer $filter  FILTER_* constant.
     * @param array   $options Fitler options.
     *
     * @return mixed
     */
    public function filter($key, $default=null, $filter=FILTER_DEFAULT, array $options=array())
    {
        $value = $this->get($key, $default);
        if (is_array($value)) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);
    }

    /**
     * ArrayAccess implementation for get().
     *
     * @param string $key Key.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * ArrayAccess implementation for set().
     *
     * @param string $key   Key.
     * @param mixed  $value Value to set.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * ArrayAccess implementation for has().
     *
     * @param string $key Key.
     *
     * @return mixed
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * ArrayAccess implementation for unset($collection).
     *
     * @param string $key Key.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        if (isset($this->collection[$key])) {
            unset($this->collection[$key]);
        }
    }
}

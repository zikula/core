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
     * Get.
     * 
     * @param string $key     Key.
     * @param mixed  $default Default value if not set.
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return (isset($this->collection[$key])) ? $this->collection[$key] : $default;
    }

    /**
     * Set.
     *
     * @param string $key
     * @param mixed  $value
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
     * @param string $key
     *
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->collection[$key]);
    }

    /**
     * ArrayAccess implementation for get().
     *
     * @param string $key
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
     * @param string $key
     * @param mixed  $value
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
     * @param string $key
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
     * @param string $key
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
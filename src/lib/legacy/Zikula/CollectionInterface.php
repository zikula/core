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

interface Zikula_CollectionInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Add an item to the collection without a key.
     *
     * @param mixed $value The value to add.
     *
     * @return mixed
     */
    public function add($value);

    /**
     * Add an item to the collection with a key.
     *
     * @param mixed $key   The key to the item within the collection.
     * @param mixed $value The value of the item.
     *
     * @return mixed
     */
    public function set($key, $value);

    /**
     * Retrieve an item from the collection by its key.
     *
     * @param mixed $key The key to the item within the collection to retrieve.
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Remove an item from the collection.
     *
     * @param mixed $key The key to the item within the collection.
     *
     * @return mixed
     */
    public function del($key);

    /**
     * Indicates whether the specified key is set within the collection.
     *
     * @param mixed $key The key to the item within the collection.
     *
     * @return boolean True if the item with the specified key is set, otherwise false.
     */
    public function has($key);

    /**
     * Indicates whether the collection is set.
     *
     * @return boolean True if set, otherwise false.
     */
    public function hasCollection();

    /**
     * Retrieve the internal collection container.
     *
     * @return mixed The collection.
     */
    public function getCollection();
}

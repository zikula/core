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

namespace Zikula\Common\Collection;

interface CollectionInterface
{
    /**
     * Add an item to the collection without a key.
     *
     * @param mixed $value The value to add
     */
    public function add($value): void;

    /**
     * Add an item to the collection with a key.
     *
     * @param mixed $key The key to the item within the collection
     * @param mixed $value The value of the item
     */
    public function set($key, $value): void;

    /**
     * Retrieve an item from the collection by its key.
     *
     * @param mixed $key The key to the item within the collection to retrieve
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Remove an item from the collection.
     *
     * @param mixed $key The key to the item within the collection
     */
    public function del($key): void;

    /**
     * Indicates whether the specified key is set within the collection.
     *
     * @param mixed $key The key to the item within the collection
     */
    public function has($key): bool;

    /**
     * Indicates whether the collection is set.
     */
    public function hasCollection(): bool;

    /**
     * Retrieve the internal collection container.
     *
     * @return mixed The collection
     */
    public function getCollection();
}

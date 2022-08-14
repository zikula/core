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

interface CollectionInterface
{
    /**
     * Add an item to the collection without a key.
     */
    public function add(mixed $value): self;

    /**
     * Add an item to the collection with a key.
     */
    public function set(mixed $key, mixed $value): self;

    /**
     * Retrieve an item from the collection by its key.
     */
    public function get(mixed $key): mixed;

    /**
     * Remove an item from the collection.
     */
    public function del(mixed $key): self;

    /**
     * Indicates whether the specified key is set within the collection.
     */
    public function has(mixed $key): bool;

    /**
     * Indicates whether the collection is set.
     */
    public function hasCollection(): bool;

    /**
     * Retrieve the internal collection container.
     */
    public function getCollection(): mixed;
}

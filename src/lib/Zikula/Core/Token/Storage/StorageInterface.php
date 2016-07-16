<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Token\Storage;

interface StorageInterface
{
    /**
     * Get token by ID.
     *
     * @param string $id Id
     *
     * @return string
     */
    public function get($id);

    /**
     * Save token.
     *
     * @param string $id        Id
     * @param string $token     Token to be saved
     * @param string $timestamp Timestamp of token
     *
     * @return void
     */
    public function save($id, $token, $timestamp);

    /**
     * Delete token by ID.
     *
     * @param string $id Id
     *
     * @return void
     */
    public function delete($id);

    /**
     * Initiate garbage collection.
     *
     * @param integer $lifetime
     * @return void
     */
    public function gc($lifetime);
}

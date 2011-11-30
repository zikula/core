<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Token
 * @subpackage Storage
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Token\Storage;

/**
 * Zikula_Token_Storage class.
 */
interface StorageInterface
{
    /**
     * Get token by ID.
     *
     * @param string $id Id.
     *
     * @return string
     */
    function get($id);

    /**
     * Save token.
     *
     * @param string $id        Id.
     * @param string $token     Token to be saved.
     * @param string $timestamp Timestamp of token.
     *
     * @return void
     */
    function save($id, $token, $timestamp);

    /**
     * Delete token by ID.
     *
     * @param string $id Id.
     *
     * @return void
     */
    function delete($id);
}

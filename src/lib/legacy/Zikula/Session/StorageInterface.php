<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Storage interface.
 *
 * @deprecated
 */
interface Zikula_Session_StorageInterface
{
    /**
     * Start session.
     *
     * @return void
     */
    public function start();

    /**
     * Expire this session gracefully.
     *
     * @return void
     */
    public function expire();

    /**
     * Regenerate session.
     *
     * @param boolean $delete Whether to delete the session or leave it to GC
     *
     * @return boolean
     */
    public function regenerate($delete = false);

    /**
     * Open session.
     *
     * @param string $savePath    Save path
     * @param string $sessionName Session Name
     *
     * @return boolean
     */
    public function open($savePath, $sessionName);

    /**
     * Close session.
     *
     * @return boolean
     */
    public function close();

    /**
     * Read session.
     *
     * @param string $sessionId Session ID
     *
     * @return boolean
     */
    public function read($sessionId);

    /**
     * Commit session to storage.
     *
     * @param string $sessionId Session ID
     * @param mixed  $vars      Variables to write
     *
     * @return boolean
     */
    public function write($sessionId, $vars);

    /**
     * Destroy this session.
     *
     * @param string $sessionId Session ID
     *
     * @return void
     */
    public function destroy($sessionId);

    /**
     * Garbage collection for storage.
     *
     * @param integer $lifetime Max lifetime to keep session stored
     *
     * @return boolean
     */
    public function gc($lifetime);
}

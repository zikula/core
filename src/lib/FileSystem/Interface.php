<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * FileSystem_Interface is the interface including all functions which individual drivers
 * must implement. This assures that all drivers operate in similar/expected ways and all
 * have roughly the same capabilities. If a function has no meaning for a driver then that
 * function must be included in the driver, however it mustregister an error when called,
 * and then return false.
 */
interface FileSystem_Interface
{
    /**
     * Setup.
     *
     * Use this to instanciate any facade driver class required.
     *
     * @return void
     */
    public function setup();

    /**
     * Interface connect function.
     *
     * For most functions errors will be regesterd on fail, See FileSystem/Error class
     * for more details.
     *
     * @return boolean True on connect, false on failure.
     */
    public function connect();

    /**
     * Interface get function.
     *
     * @param string $local  The pathname to the desired local file.
     * @param string $remote The pathname to the remote file to get.
     *
     * @return boolean True on success false on failure.
     */
    public function get($local, $remote);

    /**
     * Interface fget function.
     *
     * @param string $remote The path to the remote file.
     *
     * @return resource|bool The resource on success false on fail.
     */
    public function fget($remote);

    /**
     * Interface put function.
     *
     * @param string $local  The pathname to the local file.
     * @param string $remote The pathname to the desired remote file.
     *
     * @return boolean True on success false on failure.
     */
    public function put($local, $remote);

    /**
     * Interface fput function.
     *
     * @param stream|resource $stream The resource to put remotely, probably the resource returned from a fget.
     * @param string          $remote The pathname to the desired remote pathname.
     *
     * @return boolean|integer Number of bytes written on success, false on failure.
     */
    public function fput($stream, $remote);

    /**
     * Interface chmod function.
     *
     * @param integer $perm The permission to assign to the file, unix style (example: 777 for full permission).
     * @param string  $file The pathname to the remote file to chmod.
     *
     * @return boolean|integer The new permission or false if failed.
     */
    public function chmod($perm, $file);

    /**
     * Interface ls function.
     *
     * @param string $dir The directory to get the contents of, blank for current directory, start with / for absolute path.
     *
     * @return array|boolean An array of the contents of $dir or false if fail.
     */
    public function ls($dir = '');

    /**
     * Interface cd function.
     *
     * @param string $dir The directory on the remote machine to enter, start with '/' for absolute path.
     *
     * @return boolean True on success false on failure.
     */
    public function cd($dir = '');

    /**
     * Interface cp function.
     *
     * @param string $sourcepath The path to the original source file.
     * @param string $destpath   The path to where you want to copy the source file.
     *
     * @return boolean True on success false on failure.
     */
    public function cp($sourcepath, $destpath);

    /**
     * Interface mf function.
     *
     * @param string $sourcepath The path to the original source file.
     * @param string $destpath   The path to where you want to move the source file.
     *
     * @return boolean True on success false on failure.
     */
    public function mv($sourcepath, $destpath);

    /**
     * Interface rm function.
     *
     * @param string $sourcepath The path to the remote file to remove.
     *
     * @return boolean
     */
    public function rm($sourcepath);
}
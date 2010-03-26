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
 *
 * @author kage
 *
 */
interface FileSystem_Interface
{
    public function connect();
    public function get($local, $remote);
    public function fget($remote);
    public function put($local, $remote);
    public function fput($stream, $remote);
    public function chmod($perm, $file);
    public function ls($dir = '');
    public function cd($dir = '');
    public function cp($sourcepath, $destpath);
    public function mv($sourcepath, $destpath);
    public function rm($sourcepath);
}
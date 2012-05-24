<?php

/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package FileSystem
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_FileSystem_Local is the standard driver for Local/Direct connections.
 */
class Zikula_FileSystem_Local extends Zikula_FileSystem_AbstractDriver
{

    /**
     * Resource handle.
     *
     * @var resource
     */
    private $_resource;

    /**
     * Create local connection.
     *
     * Standard function for creating a Local connection, this must be called
     * before any of the other functions in the Zikula_FileSystem_Interface. However the construct
     * itself calles this function upon completion, which alleviates the need to ever call
     * this function manualy. For Local this function does very little, most local functions
     * will work even without the connect() function being called.
     *
     * @return bool True.
     */
    public function connect()
    {
        $this->_resource = stream_context_create();
        if ($this->configuration->getDir() == '') {
            return true;
        }
        if ($this->driver->chdir($this->configuration->getDir()) == true) {
            return true;
        }

        return false;
    }

    /**
     * Put a local file to another local target file.
     *
     * This command is an alias for the cp() command.
     *
     * @param string $local  The pathname to the local source file.
     * @param string $remote The pathname to the local target file.
     *
     * @return boolean True on success false on failure.
     */
    public function put($local, $remote)
    {
        return $this->cp($local, $remote);
    }

    /**
     * Stream/resource put.
     *
     * Similar to put but does not use a local file as the source,
     * instead it uses a stream or resource.
     *
     * @param string $stream The resource to save as a file, probably the resource returned from a fget.
     * @param string $remote The pathname to the desired local file.
     *
     * @return mixed Number of bytes written on success, false on failure.
     */
    public function fput($stream, $remote)
    {
        $this->errorHandler->start();
        if (($bytes = $this->driver->putContents($remote, $stream, 0, $this->_resource)) !== false) {
            fclose($stream);
            $this->errorHandler->stop();

            return $bytes;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Get a local file and put it to  another local target file.
     *
     * This command is an alias for the cp() or put() function,
     * but in reverse: $remote is the source and $local is the target.
     *
     * @param string $local  The pathname to the local target file.
     * @param string $remote The pathname to the local source file.
     *
     * @return boolean True on success false on failure.
     */
    public function get($local, $remote)
    {
        return $this->cp($remote, $local);
    }

    /**
     * Similar to get() but does not save the file.
     *
     * Instead it returns a
     * resource handle which can then be saved with fput(), or can be manipulated
     * in the same manner as any other file resouce handle.
     * <samp>
     * $local = new Zikula_FileSystem_Local($config);
     * $resource = $local->fget('filename.ext');
     * $local->fput($resource,'filename2.ext');
     *
     * //or
     * $contents = stream_get_contents($resource);
     * //$contents now has the contents of $resource in a text format.
     * </samp>
     *
     * @param string $remote The pathname to the local source file.
     *
     * @return mixed File resource handle or false on failure.
     */
    public function fget($remote)
    {
        $this->errorHandler->start();
        if (($handle = $this->driver->fileOpen($remote, 'r+', false, $this->_resource)) !== false) {
            rewind($handle);
            $this->errorHandler->stop();

            return $handle;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Change the permissions of a file.
     *
     * @param integer $perm The permission to assign to the file, unix style (example: 777 for full permission).
     * @param string  $file The pathname to the remote file to chmod.
     *
     * @return mixed The new permission or false if failed.
     */
    public function chmod($perm, $file)
    {
        $this->errorHandler->start();
        $perm = (int) octdec(str_pad($perm, 4, '0', STR_PAD_LEFT));
        if (($perm = $this->driver->chmod($file, $perm)) !== false) {
            $perm = (int) decoct(str_pad($perm, 4, '0', STR_PAD_LEFT));
            $this->errorHandler->stop();

            return $perm;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Get the entire contents of a directory.
     *
     * @param string $dir The directory to get the contents of, blank for current directory, start with / for absolute path.
     *
     * @return mixed An array of the contents of $dir or false if fail.
     */
    public function ls($dir = '')
    {
        $dir = ($dir == '' ? getcwd() : $dir);
        $this->errorHandler->start();
        if (($files = $this->driver->scandir($dir, 0, $this->_resource)) !== false) {
            return $files;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Change the current working directory on the Local machine.
     *
     * @param string $dir The directory on the remote machine to enter, start with '/' for absolute path.
     *
     * @return boolean True if changed dir, false otherwise.
     */
    public function cd($dir = '')
    {
        $this->errorHandler->start();
        if ($this->driver->chdir($dir)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Move a remote file to a new location on the local machine.
     *
     * This can also be used to rename files.
     *
     * @param string $sourcepath The path to the original source file.
     * @param string $destpath   The path to where you want to move the source file.
     *
     * @return boolean True on success, false on failure.
     */
    public function mv($sourcepath, $destpath)
    {
        $this->errorHandler->start();
        if ($this->driver->rename($sourcepath, $destpath, $this->_resource)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Copy a file on the local machine to a new location on the local machine.
     *
     * Similar to mv() method but leaves the original file.
     *
     * @param string $sourcepath The path to the original source file.
     * @param string $destpath   The path to where you want to copy the source file.
     *
     * @return boolean True on success, false on failure.
     */
    public function cp($sourcepath, $destpath)
    {
        $this->errorHandler->start();
        if ($this->driver->copy($sourcepath, $destpath, $this->_resource)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Remove a file from the local file system.
     *
     * @param string $sourcepath The path to the file to be removed.
     *
     * @return boolean True if file removed, false if not.
     */
    public function rm($sourcepath)
    {
        $this->errorHandler->start();
        if ($this->driver->delete($sourcepath, $this->_resource)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

}

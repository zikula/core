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
 * FileSystem_Local is the standard driver for Local/Direct connections. This class extends FileSystem_Driver
 * and thus inherits the construct and FileSystem_Error functions from FileSystem_Driver.
 * This class must implement FileSystem_Interface, the requirement to implement this interface
 * is inherited from FileSystem_Driver.
 * @author kage
 *
 */
class FileSystem_Local extends FileSystem_Driver
{
    private $resource;

    /**
     * Standard function for creating a Local connection, this must be called
     * before any of the other functions in the FileSystem_Interface. However the construct
     * itself calles this function upon completion, which alleviates the need to ever call
     * this function manualy. For Local this function does very little, most local functions
     * will work even without the connect() function being called.
     * @return Boolean
     */
    public function connect()
    {
        $this->resource = stream_context_create();
        return true;
    }

    /**
     * Put a local file to another local target file. This command is an alias
     * for the cp() command.
     *
     * @param $local	The pathname to the local source file
     * @param $remote	The pathname to the local target file
     */
    public function put($local, $remote)
    {
        return $this->cp($local, $remote);
    }

    /**
     * Similar to put but does not use a local file as the source,
     * instead it uses a stream or resource.
     * @param $stream	The resource to save as a file, probably the resource returned from a fget
     * @param $remote	The pathname to the desired local file
     *
     * @return 			number of bytes written on success, false on failure
     */
    public function fput($stream, $remote)
    {
        $this->start_handler();
        if (($bytes = file_put_contents($remote, $stream, 0, $this->resource)) !== false) {
            fclose($stream);
            $this->stop_handler();
            return $bytes;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * get a local file and put it to  another local target file.
     * This command is an alias for the cp() or put() function,
     * but in reverse: $remote is the source and $local is the target.
     *
     * @param $local	The pathname to the local target file
     * @param $remote	The pathname to the local source file
     */
    public function get($local, $remote)
    {
        return $this->cp($remote, $local);
    }

    /**
     * Similar to get() but does not save the file. instead it returns a
     * resource handle which can then be saved with fput(), or can be manipulated
     * in the same manner as any other file resouce handle.
     * eg: $local = new FileSystemLocal($conf);
     * $resource = $local->fget('filename.ext');
     * $local->fput($resource,'filename2.ext');
     * //or
     * $contents = stream_get_contents($resource);
     * //$contents now has the contents of $resource in a text format.
     *
     * @param $remote	The pathname to the local source file
     * @return 			File resource handle or false on failure
     */
    public function fget($remote)
    {
        $this->start_handler();
        if (($handle = fopen($remote, 'r+')) !== false) {
            rewind($handle);
            $this->stop_handler();
            return $handle;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Change the permissions of a file.
     *
     * @param $perm		The permission to assign to the file, unix style (example: 777 for full permission)
     * @param $file		The pathname to the remote file to chmod
     * @return 			The new permission or false if failed.
     */
    public function chmod($perm, $file)
    {
        $this->start_handler();
        $perm = (int) octdec(str_pad($perm, 4, '0', STR_PAD_LEFT));
        if (($perm = chmod($file, $perm)) !== false) {
            $perm = (int) decoct(str_pad($perm, 4, '0', STR_PAD_LEFT));
            $this->stop_handler();
            return $perm;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Get the entire contents of a directory.
     * @param	$dir the directory to get the contents of, blank for current directory, start with / for absolute path
     * @return	an array of the contents of $dir or false if fail.
     */
    public function ls($dir = "")
    {
        $dir = ($dir == "" ? getcwd() : $dir);
        $this->start_handler();
        if (($files = scandir($dir, 0, $this->resource)) !== false) {
            return $files;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Change the current working directory on the Local machine.
     * @param $dir	The directory on the remote machine to enter, start with '/' for absolute path.
     * @return 		Boolean
     */
    public function cd($dir = '')
    {
        $this->start_handler();
        if (chdir($dir)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Move a remote file to a new location on the local machine.
     * This can also be used to rename files.
     * @param $sourcepath	The path to the original source file
     * @param $destpath		The path to where you want to move the source file
     */
    public function mv($sourcepath, $destpath)
    {
        $this->start_handler();
        if (rename($sourcepath, $destpath, &$this->resource)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Copy a file on the local machine to a new location on the local machine.
     * Similar to mv() method but leaves the original file.
     * @param $sourcepath	The path to the original source file
     * @param $destpath		The path to where you want to copy the source file
     */
    public function cp($sourcepath, $destpath)
    {
        $this->start_handler();
        if (copy($sourcepath, $destpath, &$this->resource)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Remove a file from the local file system.
     *
     * @param 	$sourcepath The path to the file to be removed
     * @return	Boolean
     */
    public function rm($sourcepath)
    {
        $this->start_handler();
        if (unlink($sourcepath, &$this->resource)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    public function error_codes()
    {
        $this->stop_handler();
        $errors = array();
        return $errors;
    }
}
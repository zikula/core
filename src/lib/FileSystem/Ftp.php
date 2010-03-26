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
 * FileSystem_Ftp is the standard driver for FTP connections. This class extends FileSystem_Driver
 * and thus inherits the construct and FileSystem_Error functions from FileSystem_Driver.
 * This class must implement FileSystem_Interface, the requirement to implement this interface
 * is inherited from FileSystem_Driver.
 * @author kage
 *
 */
class FileSystem_Ftp extends FileSystem_Driver
{
    private $resource;
    private $dir = "/";

    /**
     * Standard function for creating a FTP connection and logging in, this must be called
     * before any of the other functions in the FileSystem_Interface. However the construct
     * itself calles this function upon completion, which alleviates the need to ever call
     * this function manualy.
     * @return Boolean
     */
    public function connect()
    {
        $this->start_handler();
        //create the connection
        if (($this->resource = ($this->configuration->getSSL() ? $this->resource = ftp_ssl_connect($this->configuration->getHost(), $this->configuration->getPort(), $this->configuration->getTimeout()) : ftp_connect($this->configuration->getHost(), $this->configuration->getPort(), $this->configuration->getTimeout()))) !== false) {
            //log in
            if (ftp_login($this->resource, $this->configuration->getUser(), $this->configuration->getPass())) {
                //change directory
                if (ftp_pasv($this->resource, $this->configuration->getPasv())) {
                    if (ftp_chdir($this->resource, $this->configuration->getDir())) {
                        $this->dir = ftp_pwd(&$this->resource);
                        $this->stop_handler();
                        return true;
                    }
                }
            }
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Put a local file up to a remote server.
     * This method should be used with caution because it undermines the purpose of the
     * FileSystem classes by the fact that it gets the local file without using the
     * local driver.
     *
     * @param $local	The pathname to the local file
     * @param $remote	The pathname to the desired remote file
     */
    public function put($local, $remote)
    {
        $this->isAlive(true);
        $this->start_handler();
        if (ftp_put($this->resource, $remote, $local, FTP_BINARY)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Similar to put but does not get the file localy.
     * This should be used instead of put in most cases.
     * @param $stream	The resource to put remotely, probably the resource returned from a fget
     * @param $remote	The pathname to the desired remote pathname
     *
     * @return 			number of bytes written on success, false on failure
     */
    public function fput($stream, $remote)
    {
        $this->isAlive(true);
        $this->start_handler();
        if (ftp_fput($this->resource, $remote, $stream, FTP_BINARY)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Get a remote file and save it localy, opposite of put function.
     * This method should be used with caution because it undermines the purpose of the
     * FileSystem classes by the fact that it saves the file localy without using the
     * local driver.
     *
     * @param $local	The pathname to the desired local file
     * @param $remote	The pathname to the remote file to access
     */
    public function get($local, $remote)
    {
        $this->isAlive(true);
        $this->start_handler();
        if (ftp_get($this->resource, $local, $remote, FTP_BINARY)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Similar to get but does not save file localy.
     * This should usually be used instead of get in most cases.
     * @param $remote	The path to the remote file
     * @return			The resource on success false on fail
     */
    public function fget($remote)
    {
        $this->isAlive(true);
        $this->start_handler();
        $handle = fopen('php://temp', 'r+');
        if (ftp_fget($this->resource, $handle, $remote, FTP_BINARY)) {
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
        $this->isAlive(true);
        $this->start_handler();
        $perm = (int) octdec(str_pad($perm, 4, '0', STR_PAD_LEFT));
        if (($perm = ftp_chmod($this->resource, $perm, $file)) !== false) {
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
    public function ls($dir = '')
    {
        $this->isAlive(true);
        $this->start_handler();
        $dir = ($dir == "" ? ftp_pwd(&$this->resource) : $dir);
        if (($ls = ftp_nlist($this->resource, $dir)) !== false) {
            $this->stop_handler();
            return $ls;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Change the current working directory on the remote machine.
     * @param $dir	The directory on the remote machine to enter, start with '/' for absolute path.
     * @return 		Boolean
     */
    public function cd($dir = "")
    {
        $this->isAlive(true);
        $this->start_handler();
        if (ftp_chdir($this->resource, $dir)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Move a remote file to a new location on the remote server.
     * This can also be used to rename files.
     * @param $sourcepath	The path to the original source file
     * @param $destpath		The path to where you want to move the source file
     */
    public function mv($sourcepath, $destpath)
    {
        $this->isAlive(true);
        $this->start_handler();
        if (ftp_rename($this->resource, $sourcepath, $destpath)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Copy a file on the remote server to a new location on the remote.
     * Same as mv method but leaves the original file.
     * @param $sourcepath	The path to the original source file
     * @param $destpath		The path to where you want to copy the source file
     */
    public function cp($sourcepath, $destpath)
    {
        $this->isAlive(true);
        $this->start_handler();
        if (($handle = $this->fget($sourcepath)) !== false) {
            $val = $this->fput($handle, $destpath);
            $this->stop_handler();
            return $val;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Remove a file from the remote file system.
     *
     * @param 	$sourcepath The path to the remote file to remove
     * @return	Boolean
     */
    public function rm($sourcepath)
    {
        $this->isAlive(true);
        $this->start_handler();
        if ((ftp_delete($this->resource, $sourcepath)) !== false) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    public function isAlive($reconnect = false)
    {
        if (!@ftp_systype($this->resource)) {
            if ($reconnect) {
                return $this->connect();
            }
            return false;
        }
        return true;
    }

    public function error_codes()
    {
        $this->stop_handler();
        $errors = array(
            array(
                'code' => '2',
                'search' => 'getaddrinfo failed'),
            array(
                'code' => '3',
                'search' => 'Failed to change directory'),
            array(
                'code' => '4',
                'search' => 'No such file or directory'),
            array(
                'code' => '5',
                'search' => 'Failed to open file'),
            array(
                'code' => '6',
                'search' => 'SITE CHMOD command failed'),
            array(
                'code' => '7',
                'search' => 'Could not create file'),
            array(
                'code' => '8',
                'search' => 'RNFR command failed'),
            array(
                'code' => '11',
                'search' => 'Delete operation failed'),
            array(
                'code' => '12',
                'search' => 'not a valid resource handle'));
        return $errors;
    }
}
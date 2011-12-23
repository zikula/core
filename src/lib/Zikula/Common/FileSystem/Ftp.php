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

namespace Zikula\Common\FileSystem;

/**
 * Ftp is the standard driver for FTP connections.
 */
class Ftp extends AbstractDriver
{
    /**
     * The php ftp resource handle.
     *
     * @var resource|boolean
     */
    private $resource;

    /**
     * The current working directory.
     *
     * @var string
     */
    private $dir = '/';

    /**
     * Standard function for creating a FTP connection and logging in.
     *
     * This must be called before any of the other functions in the
     * Interface. However the construct itself calles this
     * function upon completion, which alleviates the need to ever call
     * this function manualy.
     *
     * @return boolean
     */
    public function connect()
    {
        $this->errorHandler->start();

        //create the connection
        if ($this->configuration->getSSL()) {
            $this->resource = $this->driver->sslConnect($this->configuration->getHost(), $this->configuration->getPort(), $this->configuration->getTimeout());
        } else {
            $this->resource = $this->driver->connect($this->configuration->getHost(), $this->configuration->getPort(), $this->configuration->getTimeout());
        }

        if ($this->resource !== false) {
            //log in
            if ($this->driver->login($this->resource, $this->configuration->getUser(), $this->configuration->getPass())) {
                //change directory
                if ($this->driver->pasv($this->resource, $this->configuration->getPasv())) {
                    if ($this->driver->chdir($this->resource, $this->configuration->getDir())) {
                        $this->dir = ftp_pwd($this->resource);
                        $this->errorHandler->stop();

                        return true;
                    }
                }
            }
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Put a local file up to a remote server.
     *
     * This method should be used with caution because it undermines the purpose of the
     * FileSystem classes by the fact that it gets the local file without using the
     * local driver.
     *
     * @param string $local  The pathname to the local file.
     * @param string $remote The pathname to the desired remote file.
     *
     * @return boolean True if file put to remote, false if not.
     */
    public function put($local, $remote)
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        if ($this->driver->put($this->resource, $remote, $local, FTP_BINARY)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Similar to put but does not get the file localy.
     *
     * This should be used instead of put in most cases.
     *
     * @param stream|resource $stream The resource to put remotely, probably the resource returned from a fget.
     * @param string          $remote The pathname to the desired remote pathname.
     *
     * @return integer|boolean  number of bytes written on success, false on failure.
     */
    public function fput($stream, $remote)
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        if ($this->driver->fput($this->resource, $remote, $stream, FTP_BINARY)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Write the contents of a string to the remote.
     *
     * @param string $contents The contents to put remotely.
     * @param string $remote   The pathname to the desired remote pathname.
     *
     * @return boolean|integer Number of bytes written on success, false on failure.
     */
    public function file_put_contents($contents, $remote)
    {
        $stream = fopen('data://text/plain,' . $contents, 'r');

        return $this->fput($stream, $remote);
    }

    /**
     * Get the contents of a file from the remote.
     *
     * @param string $remote   The pathname to the desired remote file.
     *
     * @return string|boolean The string containing file contents on success false on fail.
     */
    public function file_get_contents($remote)
    {
        return stream_get_contents($this->fget($remote));
    }

    /**
     * Get a remote file and save it localy, opposite of put function.
     *
     * This method should be used with caution because it undermines the purpose of the
     * FileSystem classes by the fact that it saves the file localy without using the
     * local driver.
     *
     * @param string $local  The pathname to the desired local file.
     * @param string $remote The pathname to the remote file to get.
     *
     * @return bool True on success, false on failure.
     */
    public function get($local, $remote)
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        if ($this->driver->get($this->resource, $local, $remote, FTP_BINARY)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Similar to get but does not save file localy.
     *
     * This should usually be used instead of get in most cases.
     *
     * @param string $remote The path to the remote file.
     *
     * @return resource|boolean The resource on success false on fail.
     */
    public function fget($remote)
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        $handle = fopen('php://temp', 'r+');
        if ($this->driver->fget($this->resource, $handle, $remote, FTP_BINARY)) {
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
     * @return integer|boolean The new permission or false if failed.
     */
    public function chmod($perm, $file)
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        $perm = (int) octdec(str_pad($perm, 4, '0', STR_PAD_LEFT));
        if (($perm = $this->driver->chmod($this->resource, $perm, $file)) !== false) {
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
     * @return array|boolean  An array of the contents of $dir or false if fail.
     */
    public function ls($dir = '')
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        $dir = ($dir == '' ? ftp_pwd($this->resource) : $dir);
        if (($ls = $this->driver->nlist($this->resource, $dir)) !== false) {
            $this->errorHandler->stop();

            return $ls;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Change the current working directory on the remote machine.
     *
     * @param string $dir The directory on the remote machine to enter, start with '/' for absolute path.
     *
     * @return boolean
     */
    public function cd($dir = '')
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        if ($this->driver->chdir($this->resource, $dir)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Move a remote file to a new location on the remote server.
     *
     * This can also be used to rename files.
     *
     * @param string $sourcepath The path to the original source file.
     * @param string $destpath   The path to where you want to move the source file.
     *
     * @return boolean  True if file moved, false if failed.
     */
    public function mv($sourcepath, $destpath)
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        if ($this->driver->rename($this->resource, $sourcepath, $destpath)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Copy a file on the remote server to a new location on the remote.
     *
     * Same as mv method but leaves the original file.
     *
     * @param string $sourcepath The path to the original source file.
     * @param string $destpath   The path to where you want to copy the source file.
     *
     * @return boolean True on success, false on failure.
     */
    public function cp($sourcepath, $destpath)
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        if (($handle = $this->fget($sourcepath)) !== false) {
            if ($this->fput($handle, $destpath)) {
                $this->errorHandler->stop();

                return true;
            };
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Remove a file from the remote file system.
     *
     * @param string $sourcepath The path to the remote file to remove.
     *
     * @return  boolean True on success, false on failure.
     */
    public function rm($sourcepath)
    {
        $this->isAlive(true);
        $this->errorHandler->start();
        if (($this->driver->delete($this->resource, $sourcepath)) !== false) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Checks to see if connection is alive(experimental).
     *
     * Reconnects if not still alive, this function needs to
     * be fixed up.
     * TODO: make this better.
     *
     * @param boolean $reconnect Reconnect if connection is dead?.
     *
     * @return boolean True if connected, false if not.
     */
    public function isAlive($reconnect = false)
    {
        if (!$this->driver->systype($this->resource)) {
            if ($reconnect) {
                return $this->connect();
            }

            return false;
        }

        return true;
    }

    /**
     * Check if a file is writable.
     *
     * @param string $sourcepath The path to the file to check if is writable.
     *
     * @return boolean True if is writable False if not.
     */
    public function is_writable($remote_file)
    {
        $this->errorHandler->start();

        //remove slashes at beginning and end of dir's, beginning of file
        $dir = substr($this->configuration->getDir(), 0, 1) == '/' ? substr($this->configuration->getDir(), 1) : $this->configuration->getDir();
        $dir = substr($dir, -1, 1) == '/' ? substr($dir, 0, -1) : $dir;
        $remote_file = substr($remote_file, 0, 1) == '/' ? substr($remote_file, 1) : $remote_file;

        //get path info setup properly.
        $dirname = pathinfo('/' . $dir . '/' . $remote_file);
        $dirname = $dirname['dirname'];

        //get a directory listing and check that the file in question is listed (workaround for file_exists)
        $dirlist = $this->driver->nlist($this->resource, $dirname);
        if (is_array($dirlist) && in_array("/$dir/$remote_file", $dirlist)) {
            //file exists, check if we can open the file for appending
            if (!$handle = $this->driver->fopen($dir . '/' . $remote_file, 'a', $this->configuration)) {
                $this->errorHandler->stop();

                return false;
            }

            //attempt to do an empty append
            if (!fwrite($handle, '') === FALSE) {
                $this->errorHandler->stop();

                return false;
            }
            $this->errorHandler->stop();

            return true;
        }

        //file not found, return false
        $this->errorHandler->stop();

        return false;
    }

    /**
     * Determine if driver is available for use.
     *
     * @return boolean True if available, false if not.
     */
    public static function available()
    {
        return extension_loaded('ftp');
    }
}
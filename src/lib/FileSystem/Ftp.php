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
 * FileSystem_Ftp is the standard driver for FTP connections.
 *
 * This class extends FileSystem_Driver and thus inherits the construct
 * and FileSystem_Error functions from FileSystem_Driver. This class must
 * implement FileSystem_Interface, the requirement to implement this interface
 * is inherited from FileSystem_Driver.
 */
class FileSystem_Ftp extends FileSystem_Driver
{
    /**
     * The php ftp resource handle.
     *
     * @var resource|boolean
     */
    private $_resource;

    /**
     * The current working directory.
     *
     * @var string
     */
    private $_dir = '/';


    /**
     * Implement Setup.
     *
     * @return void
     */
    public function setup()
    {
        $this->driver = new FileSystem_Facade_Ftp();
    }

    /**
     * Standard function for creating a FTP connection and logging in.
     *
     * This must be called before any of the other functions in the
     * FileSystem_Interface. However the construct itself calles this
     * function upon completion, which alleviates the need to ever call
     * this function manualy.
     *
     * @return boolean
     */
    public function connect()
    {
        $this->startHandler();

        //create the connection
        if ($this->configuration->getSSL()) {
            $this->_resource = ftp_ssl_connect($this->configuration->getHost(), $this->configuration->getPort(), $this->configuration->getTimeout());
        } else {
            $this->_resource = ftp_connect($this->configuration->getHost(), $this->configuration->getPort(), $this->configuration->getTimeout());
        }

        if ($this->resource !== false) {
            //log in
            if (ftp_login($this->_resource, $this->configuration->getUser(), $this->configuration->getPass())) {
                //change directory
                if (ftp_pasv($this->_resource, $this->configuration->getPasv())) {
                    if (ftp_chdir($this->_resource, $this->configuration->getDir())) {
                        $this->_dir = ftp_pwd($this->_resource);
                        $this->stopHandler();
                        return true;
                    }
                }
            }
        }
        $this->stopHandler();
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
        $this->startHandler();
        if ($this->driver->put($this->_resource, $remote, $local, FTP_BINARY)) {
            $this->stopHandler();
            return true;
        }
        $this->stopHandler();
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
        $this->startHandler();
        if ($this->driver->fput($this->_resource, $remote, $stream, FTP_BINARY)) {
            $this->stopHandler();
            return true;
        }
        $this->stopHandler();
        return false;
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
        $this->startHandler();
        if ($this->driver->get($this->_resource, $local, $remote, FTP_BINARY)) {
            $this->stopHandler();
            return true;
        }
        $this->stopHandler();
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
        $this->startHandler();
        $handle = fopen('php://temp', 'r+');
        if ($this->driver->fget($this->_resource, $handle, $remote, FTP_BINARY)) {
            rewind($handle);
            $this->stopHandler();
            return $handle;
        }
        $this->stopHandler();
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
        $this->startHandler();
        $perm = (int)octdec(str_pad($perm, 4, '0', STR_PAD_LEFT));
        if (($perm = $this->driver->chmod($this->_resource, $perm, $file)) !== false) {
            $perm = (int)decoct(str_pad($perm, 4, '0', STR_PAD_LEFT));
            $this->stopHandler();
            return $perm;
        }
        $this->stopHandler();
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
        $this->startHandler();
        $dir = ($dir == '' ? ftp_pwd($this->_resource) : $dir);
        if (($ls = $this->driver->nlist($this->_resource, $dir)) !== false) {
            $this->stopHandler();
            return $ls;
        }
        $this->stopHandler();
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
        $this->startHandler();
        if ($this->driver->chdir($this->_resource, $dir)) {
            $this->stopHandler();
            return true;
        }
        $this->stopHandler();
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
        $this->startHandler();
        if ($this->driver->rename($this->_resource, $sourcepath, $destpath)) {
            $this->stopHandler();
            return true;
        }
        $this->stopHandler();
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
        $this->startHandler();
        if (($handle = $this->fget($sourcepath)) !== false) {
            if ($this->fput($handle, $destpath)) {
                $this->stopHandler();
                return true;
            };
        }
        $this->stopHandler();
        return false;
    }

    /**
     * Remove a file from the remote file system.
     *
     * @param string $sourcepath The path to the remote file to remove.
     *
     * @return	boolean True on success, false on failure.
     */
    public function rm($sourcepath)
    {
        $this->isAlive(true);
        $this->startHandler();
        if (($this->driver->delete($this->_resource, $sourcepath)) !== false) {
            $this->stopHandler();
            return true;
        }
        $this->stopHandler();
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
        if (!@$this->driver->systype($this->_resource)) {
            if ($reconnect) {
                return $this->connect();
            }
            return false;
        }
        return true;
    }

    /**
     * Not used at the moment.
     *
     * @return array Array of error codes.
     */
    public function errorCodes()
    {
        $this->stopHandler();
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

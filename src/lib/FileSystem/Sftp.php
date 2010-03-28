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
 * FileSystem_SFtp is the standard driver for SFTP connections.
 *
 * This class extends FileSystem_Driver
 * and thus inherits the construct and FileSystem_Error functions from FileSystem_Driver.
 * This class must implement FileSystem_Interface, the requirement to implement this interface
 * is inherited from FileSystem_Driver.
 */
class FileSystem_Sftp extends FileSystem_Driver
{
    /**
     * Resource.
     *
     * @var object
     */
    private $_resource;

    /**
     * SSH Resource.
     *
     * @var object
     */
    private $_ssh_resource;

    /**
     * Current working directory.
     *
     * @var string
     */
    private $_dir;

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
     * Standard function for creating a SFTP connection and logging in.
     *
     * This must be called before any of the other functions in the
     * FileSystem_Interface. However the construct itself calles this function
     * upon completion, which alleviates the need to ever call this function
     * manually.
     *
     * @return boolean True on connect false on failure
     */
    public function connect()
    {
        $this->startHandler();
        if (($this->_ssh_resource = ssh2_connect($this->configuration->getHost(), $this->configuration->getPort())) !== false) {
            //connected
            if ((ssh2_auth_password($this->_ssh_resource, $this->configuration->getUser(), $this->configuration->getPass())) !== false) {
                //logged in
                if (($this->_resource = ssh2_sftp($this->_ssh_resource)) !== false) {
                    //started sftp
                    if (($this->_dir = ssh2_sftp_realpath($this->_resource, $this->configuration->getDir())) !== false) {
                        //changed dir
                        $this->stopHandler();
                        return true; //return object?
                    }
                    //could not enter dir
                }
                //could not start sftp
            }
            //Could not log in
        }
        //Could not connect to host/port
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
     * @return boolean True on success false on failure.
     */
    public function put($local, $remote)
    {
        $this->startHandler();
        if (ssh2_scp_send($this->_resource, $local, $remote)) {
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
     * @param string $stream The resource to put remotely, probably the resource returned from a fget.
     * @param string $remote The pathname to the desired remote pathname.
     *
     * @return boolean|integer Number of bytes written on success, false on failure.
     */
    public function fput($stream, $remote)
    {
        $remote = ($remote == '' || substr($remote, 0, 1) !== '/' ? $this->_dir . '/' . $remote : $remote);
        $res = $this->_resource;
        $this->startHandler();
        if (($bytes = file_put_contents("ssh2.sftp://$this->_resource/$remote", $stream, 0)) !== false) {
            fclose($stream);
            $this->stopHandler();
            return $bytes;
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
     * @param string $remote The pathname to the remote file to access.
     *
     * @return boolean True on success false on failure.
     */
    public function get($local, $remote)
    {
        $this->startHandler();
        if (ssh2_scp_recv($this->_resource, $remote, $local)) {
            $this->stopHandler();
            return true;
        }
        $this->stopHandler();
        return false;
    }

    /**
     * Similar to get but does not save file locally.
     *
     * This should usually be used instead of get in most cases.
     *
     * @param string $remote The path to the remote file.
     *
     * @return resource|bool The resource on success false on fail.
     */
    public function fget($remote)
    {
        $remote = ($remote == "" || substr($remote, 0, 1) !== "/" ? $this->_dir . '/' . $remote : $remote);
        $this->startHandler();
        if (($handle = fopen("ssh2.sftp://$this->_resource/$remote", 'r+')) !== false) {
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
     * @return boolean|integer The new permission or false if failed.
     */
    public function chmod($perm, $file)
    {
        $this->startHandler();
        $file = ($file == "" || substr($file, 0, 1) !== "/" ? $this->_dir . '/' . $file : $file);
        if (($file = ssh2_sftp_realpath($this->_resource, $file)) === false) {
            $this->stopHandler(); //source file not found.
            return false;
        }
        //TODO should xterm be used?
        $shell = ssh2_shell($this->_ssh_resource, "xterm");
        fwrite($shell, "chmod --silent $perm $file;echo :::$?:::" . PHP_EOL);
        usleep(350000);
        $resp = fread($shell, 4096);
        fclose($shell);
        $this->connect(); //TODO we need a way to make sure that the connection is alive
        $matches = array();
        preg_match("/:::\d:::/", $resp, $matches);
        if (sizeof($matches) > 0) {
            switch (intval($matches[0])) {
                case 1:
                    $this->errorHandler('0', "Chmod returned with Code 1: failure.", '', '');
                    $this->stopHandler();
                    return false;
                case 0:
                    $this->stopHandler();
                    return $perm;
                default:
                    $this->stopHandler();
                    return false;
            }
        }
        //size of matches less then 1, there is no readable response
        $this->stopHandler();
        $this->errorHandler('0', "Did not get acknowledgment from host, chmod may or may not have succeeded.", '', '');
        return false;
    }

    /**
     * Get the entire contents of a directory.
     *
     * @param string $dir The directory to get the contents of, blank for current directory, start with / for absolute path.
     *
     * @return array|boolean An array of the contents of $dir or false if fail.
     */
    public function ls($dir = "")
    {
        $dir = ($dir == "" || substr($dir, 0, 1) !== "/" ? $this->_dir . '/' . $dir : $dir);

        $dir2 = "ssh2.sftp://$this->_resource/$dir";

        if (!file_exists($dir2)) {
            $this->errorRegister("$dir does not exist.", 0);
            //TODO use either errorRegister or errorHandler not both.
            return false;
        }

        if (is_dir($dir2)) {
            $handle = opendir($dir2);
            $files = array();
            while (false !== ($file = readdir($handle))) {
                if (substr("$file", 0, 1) != ".") {
                    //if (!is_dir($file)) {
                    $files[] = $file;
                    //}
                }
            }
            //finished searching the directory
            return $files;
        }
        $this->errorHandler(0, "Directory: $dir is not a directory or does not exist", 'SFtp.php', '0');
        return false;
    }

    /**
     * Change the current working directory on the remote machine.
     *
     * @param string $dir The directory on the remote machine to enter, start with '/' for absolute path.
     *
     * @return boolean True on success false on failure.
     */
    public function cd($dir = '')
    {
        $dir = ($dir == '' || substr($dir, 0, 1) !== '/' ? $this->_dir . '/' . $dir : $dir);
        $this->startHandler();
        if (($dir = ssh2_sftp_realpath($this->_resource, $dir)) !== false) {
            $this->_dir = $dir;
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
     * @return boolean True on success false on failure.
     */
    public function mv($sourcepath, $destpath)
    {
        $this->startHandler();
        $sourcepath = ($sourcepath == "" || substr($sourcepath, 0, 1) !== "/" ? $this->_dir . '/' . $sourcepath : $sourcepath);
        $destpath = ($destpath == "" || substr($destpath, 0, 1) !== "/" ? $this->_dir . '/' . $destpath : $destpath);
        if (($sourcepath = ssh2_sftp_realpath($this->_resource, $sourcepath)) !== false) {
            if ((ssh2_sftp_rename($this->_resource, $sourcepath, $destpath)) !== false) {
                //renamed file
                $this->stopHandler();
                return true;
            }
            //could not rename file
        }
        //Could not get reapath of sourcefile, it does not exist
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
     * @return boolean True on success false on failure.
     */
    public function cp($sourcepath, $destpath)
    {
        $this->startHandler();
        $sourcepath = ($sourcepath == "" || substr($sourcepath, 0, 1) !== "/" ? $this->_dir . '/' . $sourcepath : $sourcepath);
        $destpath = ($destpath == "" || substr($destpath, 0, 1) !== "/" ? $this->_dir . '/' . $destpath : $destpath);
        if (($sourcepath = ssh2_sftp_realpath($this->_resource, $sourcepath)) === false) {
            $this->stopHandler(); //source file not found.
            return false;
        }

        //TODO should xterm be used?
        $shell = ssh2_shell($this->_ssh_resource, "xterm");
        fwrite($shell, "cp $sourcepath $destpath;echo :::$?:::" . PHP_EOL);
        usleep(350000);
        $resp = fread($shell, 4096);
        fclose($shell); //the shell closes even if we dont put this, thats why next line is needed
        $this->connect(); //TODO we need a way to make sure that the connection is alive
        $matches = array();
        preg_match("/:::\d:::/", $resp, $matches);
        if (sizeof($matches) > 0) {
            switch (intval($matches[0])) {
                case 1:
                    $this->errorHandler('0', "cp returned with Code 1: failure.", '', '');
                    $this->stopHandler();
                    return false;
                case 0:
                    $this->stopHandler();
                    return true;
                default:
                    $this->stopHandler();
                    return false;
            }
        }
        //size of matches less then 1, there is no readable response
        $this->stopHandler();
        $this->errorHandler('0', "Did not get acknowledgment from host, cp may or may not have succeeded.", '', '');
        return false;
    }

    /**
     * Remove a file from the remote file system.
     *
     * @param string $sourcepath The path to the remote file to remove.
     *
     * @return boolean
     */
    public function rm($sourcepath)
    {
        $sourcepath = ($sourcepath == "" || substr($sourcepath, 0, 1) !== "/" ? $this->_dir . '/' . $sourcepath : $sourcepath);
        $this->startHandler();
        //check the file actauly exists.
        if (($sourcepath = ssh2_sftp_realpath($this->_resource, $sourcepath)) !== false) {
            //file exists
            if (ssh2_sftp_unlink($this->_resource, $sourcepath)) {
                //file deleted
                $this->stopHandler();
                return true;
            }
            //file not deleted
        }
        //file does not exist.
        $this->stopHandler();
        $this->errorHandler('0', "Could not delete: $sourcepath", '', '');
        return false;
    }

    /**
     * Errorcodes.
     *
     * @return array Array of possible error codes.
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
                'code' => '9',
                'search' => 'Permission denied'),
            array(
                'code' => '9',
                'search' => 'Authentication failed'),
            array(
                'code' => '10',
                'search' => 'Operation not permitted'),  //usually means your host has chmod disabled
            array(
                'code' => '11',
                'search' => 'Delete operation failed'),
            array(
                'code' => '12',
                'search' => 'not a valid resource handle'),  //TODO we should never get this, fix it so we dont!
            array(
                'code' => '13',
                'search' => 'Unable to resolve realpath for'));
        return $errors;
    }
}
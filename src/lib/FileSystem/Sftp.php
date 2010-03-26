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
class FileSystem_SFtp extends FileSystem_Driver
{
    /**
     * Resource.
     *
     * @var object
     */
    private $resource;

    /**
     * SSH Resource.
     *
     * @var object
     */
    private $ssh_resource;

    /**
     * Directory.
     *
     * @var string
     */
    private $dir;

    /**
     * Standard function for creating a SFTP connection and logging in.
     *
     * This must be called before any of the other functions in the
     * FileSystem_Interface. However the construct itself calles this function
     * upon completion, which alleviates the need to ever call this function
     * manually.
     *
     * @return boolean
     */
    public function connect()
    {
        $this->start_handler();
        if (($this->ssh_resource = ssh2_connect($this->configuration->getHost(), $this->configuration->getPort())) !== false) {
            //connected
            if ((ssh2_auth_password($this->ssh_resource, $this->configuration->getUser(), $this->configuration->getPass())) !== false) {
                //logged in
                if (($this->resource = ssh2_sftp($this->ssh_resource)) !== false) {
                    //started sftp
                    if (($this->dir = ssh2_sftp_realpath($this->resource, $this->configuration->getDir())) !== false) {
                        //changed dir
                        $this->stop_handler();
                        return; //how to return object?
                    }
                    //could not enter dir
                }
                //could not start sftp
            }
            //Could not log in
        }
        //Could not connect to host/port
        $this->stop_handler();
        return false;
    }

    /**
     * Put a local file up to a remote server.
     *
     * This method should be used with caution because it undermines the purpose of the
     * FileSystem classes by the fact that it gets the local file without using the
     * local driver.
     *
     * @param $local	The pathname to the local file
     * @param $remote	The pathname to the desired remote file
     */
    public function put($local, $remote)
    {
        $this->start_handler();
        if (ssh2_scp_send($this->resource, $local, $remote)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Similar to put but does not get the file localy.
     *
     * This should be used instead of put in most cases.
     *
     * @param $stream The resource to put remotely, probably the resource returned from a fget.
     * @param $remote The pathname to the desired remote pathname.
     *
     * @return mixed Number of bytes written on success, false on failure.
     */
    public function fput($stream, $remote)
    {
        $remote = ($remote == '' || substr($remote, 0, 1) !== '/' ? $this->dir . '/' . $remote : $remote);
        $res = $this->resource;
        $this->start_handler();
        if (($bytes = file_put_contents("ssh2.sftp://$this->resource/$remote", $stream, 0)) !== false) {
            fclose($stream);
            $this->stop_handler();
            return $bytes;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Get a remote file and save it localy, opposite of put function.
     *
     * This method should be used with caution because it undermines the purpose of the
     * FileSystem classes by the fact that it saves the file localy without using the
     * local driver.
     *
     * @param $local	The pathname to the desired local file.
     * @param $remote	The pathname to the remote file to access.
     *
     * @return void
     */
    public function get($local, $remote)
    {
        $this->start_handler();
        if (ssh2_scp_recv($this->resource, $remote, $local)) {
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Similar to get but does not save file locally.
     *
     * This should usually be used instead of get in most cases.
     *
     * @param $remote The path to the remote file.
     *
     * @return mixed The resource on success false on fail.
     */
    public function fget($remote)
    {
        $remote = ($remote == "" || substr($remote, 0, 1) !== "/" ? $this->dir . '/' . $remote : $remote);
        $this->start_handler();
        if (($handle = fopen("ssh2.sftp://$this->resource/$remote", 'r+')) !== false) {
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
     * @param $perm The permission to assign to the file, unix style (example: 777 for full permission).
     * @param $file The pathname to the remote file to chmod.
     *
     * @return mixed The new permission or false if failed.
     */
    public function chmod($perm, $file)
    {
        $this->start_handler();
        $file = ($file == "" || substr($file, 0, 1) !== "/" ? $this->dir . '/' . $file : $file);
        if (($file = ssh2_sftp_realpath($this->resource, $file)) === false) {
            $this->stop_handler(); //source file not found.
            return false;
        }
        //TODO should xterm be used?
        $shell = ssh2_shell($this->ssh_resource, "xterm");
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
                    $this->error_handler('0', "Chmod returned with Code 1: failure.", '', '');
                    $this->stop_handler();
                    return false;
                case 0:
                    $this->stop_handler();
                    return $perm;
                default:
                    $this->stop_handler();
                    return false;
            }
        }
        //size of matches less then 1, there is no readable response
        $this->stop_handler();
        $this->error_handler('0', "Did not get acknowledgment from host, chmod may or may not have succeeded.", '', '');
        return false;
    }

    /**
     * Get the entire contents of a directory.
     *
     * @param string $dir The directory to get the contents of, blank for current directory, start with / for absolute path.
     *
     * @return mixed Array of the contents of $dir or false if fail.
     */
    public function ls($dir = "")
    {
        $dir = ($dir == "" || substr($dir, 0, 1) !== "/" ? $this->dir . '/' . $dir : $dir);

        $dir2 = "ssh2.sftp://$this->resource/$dir";

        if (!file_exists($dir2)) {
            $this->error_register("$dir does not exist.", 0);
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
        $this->error_handler(0, "Directory: $dir is not a directory or does not exist", 'SFtp.php', '0');
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
        $dir = ($dir == '' || substr($dir, 0, 1) !== '/' ? $this->dir . '/' . $dir : $dir);
        $this->start_handler();
        if (($dir = ssh2_sftp_realpath($this->resource, $dir)) !== false) {
            $this->dir = $dir;
            $this->stop_handler();
            return true;
        }
        $this->stop_handler();
        return false;
    }

    /**
     * Move a remote file to a new location on the remote server.
     *
     * This can also be used to rename files.
     *
     * @param $sourcepath The path to the original source file.
     * @param $destpath   The path to where you want to move the source file.
     *
     * @return boolean
     */
    public function mv($sourcepath, $destpath)
    {
        $this->start_handler();
        $sourcepath = ($sourcepath == "" || substr($sourcepath, 0, 1) !== "/" ? $this->dir . '/' . $sourcepath : $sourcepath);
        $destpath = ($destpath == "" || substr($destpath, 0, 1) !== "/" ? $this->dir . '/' . $destpath : $destpath);
        if (($sourcepath = ssh2_sftp_realpath($this->resource, $sourcepath)) !== false) {
            if ((ssh2_sftp_rename($this->resource, $sourcepath, $destpath)) !== false) {
                //renamed file
                $this->stop_handler();
                return true;
            }
            //could not rename file
        }
        //Could not get reapath of sourcefile, it does not exist
        $this->stop_handler();
        return false;
    }

    /**
     * Copy a file on the remote server to a new location on the remote.
     *
     * Same as mv method but leaves the original file.
     *
     * @param $sourcepath The path to the original source file.
     * @param $destpath   The path to where you want to copy the source file.
     *
     * @return boolean
     */
    public function cp($sourcepath, $destpath)
    {
        $this->start_handler();
        $sourcepath = ($sourcepath == "" || substr($sourcepath, 0, 1) !== "/" ? $this->dir . '/' . $sourcepath : $sourcepath);
        $destpath = ($destpath == "" || substr($destpath, 0, 1) !== "/" ? $this->dir . '/' . $destpath : $destpath);
        if (($sourcepath = ssh2_sftp_realpath($this->resource, $sourcepath)) === false) {
            $this->stop_handler(); //source file not found.
            return false;
        }

        //TODO should xterm be used?
        $shell = ssh2_shell($this->ssh_resource, "xterm");
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
                    $this->error_handler('0', "cp returned with Code 1: failure.", '', '');
                    $this->stop_handler();
                    return false;
                case 0:
                    $this->stop_handler();
                    return true;
                default:
                    $this->stop_handler();
                    return false;
            }
        }
        //size of matches less then 1, there is no readable response
        $this->stop_handler();
        $this->error_handler('0', "Did not get acknowledgment from host, cp may or may not have succeeded.", '', '');
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
        $sourcepath = ($sourcepath == "" || substr($sourcepath, 0, 1) !== "/" ? $this->dir . '/' . $sourcepath : $sourcepath);
        $this->start_handler();
        //check the file actauly exists.
        if (($sourcepath = ssh2_sftp_realpath($this->resource, $sourcepath)) !== false) {
            //file exists
            if (ssh2_sftp_unlink($this->resource, $sourcepath)) {
                //file deleted
                $this->stop_handler();
                return true;
            }
            //file not deleted
        }
        //file does not exist.
        $this->stop_handler();
        $this->error_handler('0', "Could not delete: $sourcepath", '', '');
        return false;
    }

    /**
     * Errorcodes.
     *
     * @retrin array Array of possible error codes.
     */
    private function error_codes()
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
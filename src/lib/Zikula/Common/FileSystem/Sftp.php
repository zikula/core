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
 * Sftp is the standard driver for SFTP connections.
 */
class Sftp extends AbstractDriver
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
    private $sshResource;

    /**
     * Current working directory.
     *
     * @var string
     */
    private $dir;

    /**
     * Shell type to use when creating a ssh shell.
     *
     * @var string
     */
    private $terminal = 'xterm';

    /**
     * Standard function for creating a SFTP connection and logging in.
     *
     * This must be called before any of the other functions in the
     * Interface. However the construct itself calles this function
     * upon completion, which alleviates the need to ever call this function
     * manually.
     *
     * @return boolean True on connect false on failure
     */
    public function connect()
    {
        $this->errorHandler->start();
        $methods = array();
        if ($this->configuration->getAuthType() !== 'pass') {
            $methods['hostkey'] = $this->configuration->getAuthType();
        }
        if (($this->sshResource = $this->driver->connect($this->configuration->getHost(), $this->configuration->getPort(), $methods)) !== false) {
            //connected
            if ($this->configuration->getAuthType() !== 'pass') {
                $auth = $this->driver->authPubkey(
                    $this->sshResource, $this->configuration->getUser(), $this->configuration->getPubKey(), $this->configuration->getPrivKey(), $this->configuration->getPassphrase());
            } else {
                $auth = $this->driver->authPassword($this->sshResource, $this->configuration->getUser(), $this->configuration->getPass());
            }
            if ($auth !== false) {
                //logged in
                if (($this->resource = $this->driver->sftpStart($this->sshResource)) !== false) {
                    //started sftp
                    if (($this->dir = $this->driver->realpath($this->resource, $this->configuration->getDir())) !== false) {
                        //changed dir
                        $this->errorHandler->stop();

                        return true;
                    }
                    //could not enter dir
                }
                //could not start sftp
            }
            //Could not log in
        }

        //Could not connect to host/port
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
     * @return boolean True on success false on failure.
     */
    public function put($local, $remote)
    {
        $this->errorHandler->start();
        if ($this->driver->scpSend($this->resource, $local, $remote)) {
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
     * @param string $stream The resource to put remotely, probably the resource returned from a fget.
     * @param string $remote The pathname to the desired remote pathname.
     *
     * @return boolean|integer Number of bytes written on success, false on failure.
     */
    public function fput($stream, $remote)
    {
        if ($remote == '' || substr($remote, 0, 1) !== '/') {
            $remote = $this->dir . '/' . $remote;
        }
        $this->errorHandler->start();
        if (($bytes = $this->driver->putContents($this->resource, $remote, $stream)) !== false) {
            fclose($stream);
            $this->errorHandler->stop();

            return $bytes;
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
    public function putContents($contents, $remote)
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
    public function getContents($remote)
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
     * @param string $remote The pathname to the remote file to access.
     *
     * @return boolean True on success false on failure.
     */
    public function get($local, $remote)
    {
        $this->errorHandler->start();
        if ($this->driver->scpRecv($this->resource, $remote, $local)) {
            $this->errorHandler->stop();

            return true;
        }
        $this->errorHandler->stop();

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
        if ($remote == '' || substr($remote, 0, 1) !== '/') {
            $remote = $this->dir . '/' . $remote;
        }
        $this->errorHandler->start();
        if (($handle = $this->driver->sftpFopen($this->resource, $remote, 'r+')) !== false) {
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
     * @return boolean|integer The new permission or false if failed.
     */
    public function chmod($perm, $file)
    {
        $this->errorHandler->start();
        if ($file == '' || substr($file, 0, 1) !== '/') {
            $file = $this->dir . '/' . $file;
        }
        //make sure that $perm is numeric, this also stops injection
        if (!is_numeric($perm)) {
            $this->errorHandler->register('permission "' . $perm . '" must be numeric.');

            return false;
        }

        $perm = intval($perm);

        if (($file = $this->driver->realpath($this->resource, $file)) === false) {
            $this->errorHandler->stop(); //source file not found.

            return false;
        }

        if (($shell = $this->driver->sshShell($this->sshResource, $this->terminal)) == false) {
            return false; //could not get shell.
        }

        if ($this->driver->sshShellWrite($shell, "chmod $perm $file;echo :::$?:::" . PHP_EOL) === false) {
            return false; //couldnt write to shell
        }

        usleep(350000);
        if (($resp = $this->driver->sshShellRead($shell, 4096)) === false) {
            return false; //could not read from shell
        }

        fclose($shell); //the shell closes even if we dont put this, thats why next line is needed
        $this->connect(); //TODO we need a way to make sure that the connection is alive
        $matches = array();
        preg_match("/:::\d:::/", $resp, $matches);
        if (sizeof($matches) > 0) {
            switch (intval(str_replace(':', '', $matches[0]))) {
                case 1:
                    $this->errorHandler->register('Chmod returned with Code 1: failure.', 0);
                    $this->errorHandler->stop();

                    return false;
                case 0:
                    $this->errorHandler->stop();

                    return $perm;
                default:
                    $this->errorHandler->stop();

                    return false;
            }
        }
        //size of matches less then 1, there is no readable response
        $this->errorHandler->stop();
        $this->errorHandler->register('Did not get acknowledgment from host, chmod may or may not have succeeded.', 0);

        return false;
    }

    /**
     * Get the entire contents of a directory.
     *
     * @param string $dir The directory to get the contents of, blank for current directory, start with / for absolute path.
     *
     * @return array|boolean An array of the contents of $dir or false if fail.
     */
    public function ls($dir = '')
    {
        if ($dir == '' || substr($dir, 0, 1) !== '/') {
            $dir = $this->dir . '/' . $dir;
        }

        if ($this->driver->sftpIsDir($this->resource, $dir)) {
            $handle = $this->driver->sftpOpenDir($this->resource, $dir);
            $files = array();
            while (false !== ($file = $this->driver->sftpReadDir($handle))) {
                if (substr("$file", 0, 1) != '.') {
                    $files[] = $file;
                }
            }

            //finished searching the directory
            return $files;
        }

        //if IsDir fails that means its either not a directory or doesnt exist
        if (!$this->driver->sftpFileExists($this->resource, $dir)) {
            $this->errorHandler->register("$dir does not exist.", 0);

            return false;
        }

        $this->errorHandler->register("$dir is not a directory", 0);

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
        if ($dir == '' || substr($dir, 0, 1) !== '/') {
            $dir = $this->dir . '/' . $dir;
        }

        $this->errorHandler->start();
        if (($dir = $this->driver->realpath($this->resource, $dir)) !== false) {
            $this->dir = $dir;
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
     * @return boolean True on success false on failure.
     */
    public function mv($sourcepath, $destpath)
    {
        $this->errorHandler->start();
        if ($sourcepath == '' || substr($sourcepath, 0, 1) !== '/') {
            $sourcepath = $this->dir . '/' . $sourcepath;
        }

        if ($destpath == '' || substr($destpath, 0, 1) !== '/') {
            $destpath = $this->dir . '/' . $destpath;
        }

        if (($sourcepath = $this->driver->realpath($this->resource, $sourcepath)) !== false) {
            if (($this->driver->sftpRename($this->resource, $sourcepath, $destpath)) !== false) {
                $this->errorHandler->stop(); //renamed file

                return true;
            }//could not rename file
        }//Could not get reapath of sourcefile, it does not exist

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
     * @return boolean True on success false on failure.
     */
    public function cp($sourcepath, $destpath)
    {
        $this->errorHandler->start();
        if ($sourcepath == '' || substr($sourcepath, 0, 1) !== '/') {
            $sourcepath = $this->dir . '/' . $sourcepath;
        }

        if ($destpath == '' || substr($destpath, 0, 1) !== '/') {
            $destpath = $this->dir . '/' . $destpath;
        }

        if (($sourcepath = $this->driver->realpath($this->resource, $sourcepath)) === false) {
            $this->errorHandler->stop(); //source file not found.

            return false;
        }

        if (($shell = $this->driver->sshShell($this->sshResource, $this->terminal)) == false) {
            return false; //could not get shell.
        }

        if ($this->driver->sshShellWrite($shell, "cp $sourcepath $destpath;echo :::$?:::" . PHP_EOL) === false) {
            return false; //couldnt write to shell
        }

        usleep(350000);
        if (($resp = $this->driver->sshShellRead($shell, 4096)) === false) {
            return false; //could not read from shell
        }

        fclose($shell); //the shell closes even if we dont put this, thats why next line is needed
        $this->connect(); //TODO we need a way to make sure that the connection is alive
        $matches = array();
        preg_match("/:::\d:::/", $resp, $matches);
        if (sizeof($matches) > 0) {
            switch (str_replace(':', '', $matches[0])) {
                case 1:
                    $this->errorHandler->register('cp returned with Code 1: failure.', 0);
                    $this->errorHandler->stop();

                    return false;
                case 0:
                    $this->errorHandler->stop();

                    return true;
                default:
                    $this->errorHandler->stop();

                    return false;
            }
        } //size of matches less then 1, there is no readable response

        $this->errorHandler->stop();
        $this->errorHandler->register('Did not get acknowledgment from host, cp may or may not have succeeded.', 0);

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
        if ($sourcepath == '' || substr($sourcepath, 0, 1) !== '/') {
            $sourcepath = $this->dir . '/' . $sourcepath;
        }

        $this->errorHandler->start();
        //check the file actauly exists.
        if (($sourcepath = $this->driver->realpath($this->resource, $sourcepath)) !== false) {
            //file exists
            if ($this->driver->sftpDelete($this->resource, $sourcepath)) {
                //file deleted
                $this->errorHandler->stop();

                return true;
            } //file not deleted
        } //file does not exist.

        $this->errorHandler->stop();
        $this->errorHandler->register("Could not delete: $sourcepath", 0);

        return false;
    }

    /**
     * Check if a file is writable.
     *
     * @param string $sourcepath The path to the file to check if is writable.
     *
     * @return boolean True if is writable False if not.
     */
    public function isWritable($sourcepath)
    {
        $this->errorHandler->start();
        if ($this->driver->is_writable($sourcepath)) {
            $this->errorHandler->stop();

            return true;
        }

        $this->errorHandler->stop();

        return false;
    }

    /**
     * Determine if driver is available for use.
     *
     * @return boolean True if available, false if not.
     */
    public static function isAvailable()
    {
        return extension_loaded('ssh2');
    }

}
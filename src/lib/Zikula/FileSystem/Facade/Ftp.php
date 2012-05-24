<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package FileSystem
 * @subpackage Zikula_FileSystem_Facade
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_FileSystem_Facade_Ftp is a facade interface for FTP connections.
 *
 * Created especially to allow easy unit testing.
 */
class Zikula_FileSystem_Facade_Ftp
{
    /**
     * Facade for ftp_connect.
     *
     * @param string $host    Hostname to connect to.
     * @param int    $port    Port to connect on.
     * @param int    $timeout Timeout in seconds.
     *
     * @return boolean True on success
     */
    public function connect($host, $port = 21, $timeout = 10)
    {
        //@codeCoverageIgnoreStart
        return ftp_connect($host, $port, $timeout);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_ssl_connect.
     *
     * @param string $host    Hostname to connect to.
     * @param int    $port    Port to connect on.
     * @param int    $timeout Timeout in seconds.
     *
     * @return boolean True on success
     */
    public function sslConnect($host, $port = 21, $timeout = 10)
    {
        //@codeCoverageIgnoreStart
        return ftp_ssl_connect($host, $port, $timeout);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_login.
     *
     * @param resource $ftp_stream Ftp Resource to login to.
     * @param string   $username   Username to login with.
     * @param string   $password   Password to login with.
     *
     * @return boolean True on success.
     */
    public function login($ftp_stream, $username, $password)
    {
        //@codeCoverageIgnoreStart
        return ftp_login($ftp_stream, $username, $password);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_pasv.
     *
     * @param resource $ftp_stream Ftp resource to set pasv for.
     * @param boolean  $pasv       True for passive, false for not.
     *
     * @return boolean True on success.
     */
    public function pasv($ftp_stream, $pasv)
    {
        //@codeCoverageIgnoreStart
        return ftp_pasv($ftp_stream, $pasv);
        //@codeCoverageIgnoreEnd
    }
    /**
     * Facade for ftp_put().
     *
     * @param resource $ftp_stream  The ftp resource.
     * @param string   $remote_file The remote file to save as.
     * @param string   $local_file  The local file to put.
     * @param integer  $mode        The transfer mode.
     * @param integer  $startpos    The starting position.
     *
     * @return boolean True on success.
     */
    public function put($ftp_stream, $remote_file, $local_file, $mode, $startpos=0)
    {
        //@codeCoverageIgnoreStart
        return ftp_put($ftp_stream, $remote_file, $local_file, $mode, $startpos);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_fput().
     *
     * @param resource $ftp_stream  The ftp resource.
     * @param string   $remote_file The remote file to put to.
     * @param resource $handle      The stream to put to $remote_file.
     * @param integer  $mode        The transfer mode.
     *
     * @return boolean True on success.
     */
    public function fput($ftp_stream, $remote_file, $handle, $mode = FTP_BINARY)
    {
        //@codeCoverageIgnoreStart
        return fput($ftp_stream, $remote_file, $handle, $mode);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_get().
     *
     * @param resource $ftp_stream  The ftp resource.
     * @param string   $local_file  The local file to save to.
     * @param string   $remote_file The remote file to get.
     * @param integer  $mode        The transfer mode.
     * @param integer  $resumepos   The resume position.
     *
     * @return boolean True on success.
     */
    public function get($ftp_stream, $local_file, $remote_file, $mode, $resumepos = 0)
    {
        //@codeCoverageIgnoreStart
        return fget($ftp_stream, $local_file, $remote_file, $mode, $resumepos);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_fget().
     *
     * @param resource $ftp_stream  The ftp resource.
     * @param resource $handle      The resource to get the data into.
     * @param string   $remote_file The remote file to fget.
     * @param integer  $mode        Transfer mode.
     * @param integer  $resumepos   The resume position.
     *
     * @return boolean True on success.
     */
    public function fget($ftp_stream, $handle, $remote_file, $mode, $resumepos = 0)
    {
        //@codeCoverageIgnoreStart
        return ftp_fget($ftp_stream, $handle, $remote_file, $mode, $resumepos = 0);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_chmod().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param integer  $mode       The permission.
     * @param string   $filename   The filename to chmod.
     *
     * @return boolean True on success.
     */
    public function chmod($ftp_stream, $mode, $filename)
    {
        //@codeCoverageIgnoreStart
        return ftp_chmod($ftp_stream, $mode, $filename);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_put().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param string   $directory  The directory to list.
     *
     * @return boolean|array Array of contents on success, false on failure.
     */
    public function nlist($ftp_stream, $directory)
    {
        //@codeCoverageIgnoreStart
        return ftp_nlist($ftp_stream, $directory);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_chdir().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param string   $directory  The directory to change into.
     *
     * @return boolean True on success.
     */
    public function chdir($ftp_stream, $directory)
    {
        //@codeCoverageIgnoreStart
        return ftp_chdir($ftp_stream, $directory);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_rename().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param string   $oldname    The old filename.
     * @param string   $newname    The new filename.
     *
     * @return boolean True on success.
     */
    public function rename($ftp_stream, $oldname, $newname)
    {
        //@codeCoverageIgnoreStart
        return ftp_rename($ftp_stream, $oldname, $newname);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_delete().
     *
     * @param resource $ftp_stream The ftp resource.
     * @param string   $path       The path or file to be removed.
     *
     * @return boolean True on success.
     */
    public function delete($ftp_stream, $path)
    {
        //@codeCoverageIgnoreStart
        return delete($ftp_stream, $path);
        //@codeCoverageIgnoreEnd
    }

    /**
     * Facade for ftp_systype().
     *
     * @param resource $ftp_stream The ftp resource.
     *
     * @return boolean True on ftp systype.
     */
    public function systype($ftp_stream)
    {
        //@codeCoverageIgnoreStart
        return ftp_systype($ftp_stream);
        //@codeCoverageIgnoreEnd
    }
}

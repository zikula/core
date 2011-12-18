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

namespace Zikula\Common\FileSystem\Facade;

/**
 * SftpFacade is a facade interface for SFTP connections.
 *
 * Created especially to allow easy unit testing.
 *
 * @codeCoverageIgnore
 */
class SftpFacade
{
    /**
     * Facade for ssh2_connect.
     *
     * TODO methods and callbacks, check php api.
     *
     * @param string  $host    The host to connect to.
     * @param intiger $port    The port to connect on.
     * @param array   $methods Associative array of methods, see php ssh2_connect api docs.
     *
     * @return boolean True if connected.
     */
    public function connect($host, $port = 22, $methods = array())
    {
        return ssh2_connect($host, $port, $methods);
    }

    /**
     * Facade for ssh2_auth_password.
     *
     * @param resource $session  The resource to login to.
     * @param string   $username The username to login with.
     * @param string   $password The password to login with.
     *
     * @return boolean True if logged in.
     */
    public function authPassword($session, $username, $password)
    {
        return ssh2_auth_password($session, $username, $password);
    }

    /**
     * Facade for ssh2_auth_pubkey_file.
     *
     * @param resource $session    The resource to login to.
     * @param string   $username   The username to login with.
     * @param string   $pubkey     The path to the public key to use.
     * @param string   $privkey    The path to the private key.
     * @param string   $passphrase The passphrase for the key.
     *
     * @return boolean True if logged in.
     */
    public function authPubkey($session, $username, $pubkey, $privkey, $passphrase)
    {
        return ssh2_auth_pubkey_file($session, $username, $pubkey, $privkey, $passphrase);
    }

    /**
     * Facade for ssh2_sftp.
     *
     * @param resource $session The ssh resource to open sftp for.
     *
     * @return resource|boolean Sftp resource on success false on failure.
     */
    public function sftpStart($session)
    {
        return ssh2_sftp($session);
    }

    /**
     * Facade for ssh2_sftp_realpath.
     *
     * TODO: can probably get rid of file exists because realpath will fail if it doesnt.
     *
     * @param resource $sftp     The sftp resource to use.
     * @param string   $filename The filename to realpath.
     *
     * @return string|boolean String realpath on success, false on failure.
     */
    public function realpath($sftp, $filename)
    {
        return ssh2_sftp_realpath($sftp, $filename);
    }

    /**
     * Facade for ssh2_scp_send.
     *
     * @param resource $session     The sftp resource to use.
     * @param string   $local_file  The local file to send.
     * @param string   $remote_file The remote path to write to.
     * @param int      $create_mode The permission of the remote file.
     *
     * @return boolean True on success.
     */
    public function scpSend($session, $local_file, $remote_file, $create_mode = 0644)
    {
        return ssh2_scp_send($session, $local_file, $remote_file, $create_mode);
    }

    /**
     * Facade for file_put_contents over ssh2.
     *
     * @param resource $resource    The sftp resource to use.
     * @param string   $remote_file The remote path to write to.
     * @param resource $stream      The stream to write.
     *
     * @return intiger|boolean Bytes written on success, False on failure.
     */
    public function putContents($resource, $remote_file, $stream)
    {
        return file_put_contents("ssh2.sftp://$resource/$remote_file", $stream, 0);
    }

    /**
     * Facade for file_put_contents over ssh2.
     *
     * @param resource $session     The sftp resource to use.
     * @param string   $remote_file The remote path to get from.
     * @param string   $local_file  The local file to write to.
     *
     * @return boolean True on success.
     */
    public function scpRecv($session, $remote_file, $local_file)
    {
        return ssh2_scp_recv($session, $remote_file, $local_file);
    }

    /**
     * Facade for fopen over ssh2.
     *
     * @param resource $resource    The sftp resource to use.
     * @param string   $remote_file The remote path to open.
     * @param string   $mode        The fopen mode.
     *
     * @return resource|boolean Resource handle on success,False on failure.
     */
    public function sftpFopen($resource, $remote_file, $mode = 'r+')
    {
        return fopen("ssh2.sftp://$resource/$remote_file", $mode);
    }

    /**
     * Facade for file_exists over ssh2.
     *
     * @param resource $resource The sftp resource to use.
     * @param string   $dir      The remote file or directory to check.
     *
     * @return boolean True on success.
     */
    public function sftpFileExists($resource, $dir)
    {
        $dir2 = "ssh2.sftp://$resource/$dir";

        return file_exists($dir2);
    }

    /**
     * Facade for is_dir over ssh2.
     *
     * @param resource $resource The sftp resource to use.
     * @param string   $dir      The remote path to check if is directory.
     *
     * @return boolean True on directory.
     */
    public function sftpIsDir($resource, $dir)
    {
        $dir2 = "ssh2.sftp://$resource/$dir";

        return is_dir($dir2);
    }

    /**
     * Facade for opendir over ssh2.
     *
     * @param resource $resource The sftp resource to use.
     * @param string   $dir      The remote file or directory to check.
     *
     * @return boolean True on success.
     */
    public function sftpOpenDir($resource, $dir)
    {
        $dir2 = "ssh2.sftp://$resource/$dir";

        return opendir($dir2);
    }

    /**
     * Facade for readdir over ssh2.
     *
     * Note that this has to be called in a loop to get all contents.
     *
     * @param resource $handle The directory handle (probably returned from sftpOpenDir()).
     *
     * @return string|boolean string with filename on success, False on failure or no more content.
     */
    public function sftpReadDir($handle)
    {
        return readdir($handle);
    }

    /**
     * Facade for ssh2_sftp_rename.
     *
     * @param resource $resource    The sftp resource to use.
     * @param string   $source_path The remote source path.
     * @param string   $dest_path   The remote destination path.
     *
     * @return boolean True on success.
     */
    public function sftpRename($resource, $source_path, $dest_path)
    {
        return ssh2_sftp_rename($resource, $source_path, $dest_path);
    }

    /**
     * Facade for ssh2_sftp_delete.
     *
     * @param resource $resource    The sftp resource to use.
     * @param string   $source_path The remote source path to delete.
     *
     * @return boolean True on success.
     */
    public function sftpDelete($resource, $source_path)
    {
        return ssh2_sftp_unlink($resource, $source_path);
    }

    /**
     * Facade for ssh2_shell function.
     *
     * @param resource $resource The SSH resource to use.
     * @param string   $type     The enviroment to use(eg:vanill or xterm).
     *
     * @return resoure|boolean Shell resource on success, false on failure.
     */
    public function sshShell($resource, $type = "xterm")
    {
        return ssh2_shell($resource, $type);
    }

    /**
     * Facade for fwrite over the shell.
     *
     * @param resource $resource The SSH Shell resource to use.
     * @param string   $command  The command to send to server (end it with PHP_EOL).
     *
     * @return intiger|boolean Bytes written on success, false on failure.
     */
    public function sshShellWrite($resource, $command)
    {
        return fwrite($resource, $command);
    }

    /**
     * Facade for fwrite over the shell.
     *
     * @param resource $resource The SSH Shell resource to use.
     * @param intiger  $length   Number of bytes to read.
     *
     * @return string|boolean Response read from shell on success, false on failure.
     */
    public function sshShellRead($resource, $length = 4096)
    {
        return fread($resource, $length);
    }

    /**
     * Facade for the is_writable function.
     *
     * @param string   $filename Path to check
     *
     * @return boolean True if is writable False if not.
     */
    public function is_writable($filename)
    {
        return is_writable("ssh2.sftp://$resource/$remote_file");
    }

}
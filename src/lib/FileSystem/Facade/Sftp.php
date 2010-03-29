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
 * FileSystem_Facade_Ftp is a facade interface for FTP connections.
 *
 * Created especially to allow easy unit testing.
 */
class FileSystem_Facade_Sftp
{
	//TODO methods and callbacks, check php api.
	public function connect($host, $port = 22)
	{
		return ssh2_connect($host, $port);
	}
	
	public function auth_password($session, $username, $password)
	{
		return ssh2_auth_password($session, $username, $password);
	}
	
	public function sftp($session)
	{
		return ssh2_sftp($session);
	}
	
	public function realpath($sftp, $filename)
	{
		return ssh2_sftp_realpath($sftp, $filename);
	}
	
	public function scp_send($session, $local_file, $remote_file, $create_mode = 0644)
	{
		return ssh2_scp_send($session, $local_file, $remote_file);
	}
	
	public function put_contents($resource, $remote_file, $stream)
	{
		return file_put_contents("ssh2.sftp://$resource/$remote_file", $stream, 0);
	}
	
	public function scp_recv($session, $remote_file, $local_file)
	{
		return ssh2_scp_recv($session, $remote_file, $local_file);
	}
	
	public function sftp_fopen($resource, $remote_file, $mode = 'r+')
	{
		return fopen("ssh2.sftp://$resource/$remote_file", $mode);
	}
	
	public function sftpFileExists($resource, $dir)
	{
		$dir2 = "ssh2.sftp://$resource/$dir";
		return file_exists($dir2);
	}
	
	public function sftpIsDir($resource, $dir)
	{
		$dir2 = "ssh2.sftp://$resource/$dir";
        return is_dir($dir2);
	}
	
	public function sftpOpenDir($resource, $dir)
	{
		$dir2 = "ssh2.sftp://$resource/$dir";
		return opendir($dir2);
	}
	
	public function sftpReadDir($handle)
	{
		return readdir($handle);
	}
	
	public function sftpRename($resource, $source_path, $dest_path)
	{
		return ssh2_sftp_rename($resource, $source_path, $dest_path);
	}
	
	public function sftpDelete($resource, $source_path)
	{
		return ssh2_sftp_unlink($resource, $source_path);
	}
	
}

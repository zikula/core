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
 * Configuration class for FTP driver. please see documentation for FileSystem_Configuration
 * for more details on configuration classes. This class extends FileSystem_Configuration.
 * The only purpose to this class is to provide a configuration object to be used by the
 * ftp driver.
 * @author kage
 *
 */
class FileSystem_Configuration_Ftp extends FileSystem_Configuration
{
    protected $host = "localhost";
    protected $user = "Anonymous";
    protected $pass = "";
    protected $dir = "/";
    protected $port = "21";
    protected $timeout = "10";
    protected $ssl = false;
    protected $pasv = true;

    /**
     *	Constructs a configuration for the FTP driver.
     *
     * @param String $host The host to connect to.
     * @param String $user The username when connecting (default = Anonymous)
     * @param String $pass The password associated with $user (default = "")
     * @param String $dir  The directory on which to enter immediatly after connecting (default = "/") (optional)
     * @param Int $port    The port to use when connecting to $host (default = 21 if ftp or 990 if $sftp = true) (optional)
     * @param Int $timeout The timeout in seconds for the connection (default = 10) (optional)
     * @param Bool $ssl	   True to use FTPS false for normal FTP (default = false) (optional)
     * @param Bool $pasv   True to enable passive mode, false for active mode (default = true) (optional)
     */
    public function __construct($host = 'localhost', $user = "Anonymous", $pass
    = "", $dir = "", $port = "", $timeout = "10", $ssl = false, $pasv = true)
    {
        $this->host = ($host == "" ? 'localhost' : $host);
        $this->user = $user;
        $this->pass = $pass;
        $this->dir = ($dir == "" ? '/' : $dir);
        $this->port = ($port == "" || !is_numeric($port) ? ($ssl ? '990' : '21')
        : $port);
        $this->timeout = ($timeout == '' || !is_numeric($timeout) ? '10' :
        $timeout);
        $this->ssl = (is_bool($ssl) ? $ssl : false);
        $this->pasv = (is_bool($pasv) ? $pasv : true);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getDir()
    {
        return $this->dir;
    }
    
    public function getTimeout()
    {
        return $this->timeout;
    }
    
    public function getSSL()
    {
        return $this->ssl;
    }
    
    public function getPasv()
    {
        return $this->pasv;
    }
}
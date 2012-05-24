<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package FileSystem
 * @subpackage Zikula_FileSystem_Configuration
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * FTP Configuration class.
 *
 * Configuration class for FTP driver. please see documentation for Zikula_FileSystem_Configuration
 * for more details on configuration classes. This class implements Zikula_FileSystem_Configuration.
 * The only purpose to this class is to provide a configuration object to be used by the
 * ftp driver.
 */
class Zikula_FileSystem_Configuration_Ftp implements Zikula_FileSystem_ConfigurationInterface
{
    /**
     * FTP host.
     *
     * @var string
     */
    protected $host;

    /**
     * FTP user.
     *
     * @var string
     */
    protected $user;

    /**
     * FTP Directory.
     *
     * @var string
     */
    protected $pass;

    /**
     * FTP Directory.
     *
     * @var string
     */
    protected $dir = "/";

    /**
     * FTP Port.
     *
     * @var integer
     */
    protected $port;

    /**
     * FTP Timeout in seconds.
     *
     * @var integer
     */
    protected $timeout;

    /**
     * SSL flag.
     *
     * @var boolean
     */
    protected $ssl;

    /**
     * PASV flag.
     *
     * @var boolean
     */
    protected $pasv;

    /**
     * Constructs a configuration for the FTP driver.
     *
     * @param String  $host    The host to connect to.
     * @param String  $user    The username when connecting (default = Anonymous).
     * @param String  $pass    The password associated with $user (default = "").
     * @param String  $dir     The directory on which to enter immediatly after connecting (default = "/") (optional).
     * @param integer $port    The port to use when connecting to $host (default = 21 if ftp or 990 if $sftp = true) (optional).
     * @param integer $timeout The timeout in seconds for the connection (default = 10) (optional).
     * @param boolean $ssl     True to use FTPS false for normal FTP (default = false) (optional).
     * @param boolean $pasv    True to enable passive mode, false for active mode (default = true) (optional).
     */
    public function __construct($host = 'localhost', $user = "Anonymous", $pass = '', $dir = '', $port = 21, $timeout = 10, $ssl = false, $pasv = true)
    {
        $this->host = ($host == "" ? 'localhost' : $host);
        $this->user = $user;
        $this->pass = $pass;
        $this->dir = ($dir == "" ? '/' : $dir);
        $this->port = ($port == "" || !is_numeric($port) ? ($ssl ? '990' : '21') : $port);
        $this->timeout = ($timeout == '' || !is_numeric($timeout) ? '10' : $timeout);
        $this->ssl = (is_bool($ssl) ? $ssl : false);
        $this->pasv = (is_bool($pasv) ? $pasv : true);
    }

    /**
     * Get user property.
     *
     * @return string User.
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get pass property.
     *
     * @return string Password.
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Get host property.
     *
     * @return string Host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get FTP port property.
     *
     * @return integer Port number.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get directory property.
     *
     * @return string Directory.
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Get timeout property.
     *
     * @return integer Timeout value in seconds.
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Get SSL property.
     *
     * @return boolean True if SSL set.
     */
    public function getSSL()
    {
        return $this->ssl;
    }

    /**
     * Get PASV setting.
     *
     * @return boolean True if PASV mode set.
     */
    public function getPasv()
    {
        return $this->pasv;
    }
}

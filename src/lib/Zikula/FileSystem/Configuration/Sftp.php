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
 * SFTP Configuration class.
 *
 * Configuration class for SFTP driver. please see documentation for Zikula_FileSystem_Configuration
 * for more details on configuration classes. This class implments Zikula_FileSystem_Configuration.
 * The only purpose to this class is to provide a configuration object to be used by the
 * sftp driver.
 */
class Zikula_FileSystem_Configuration_Sftp implements Zikula_FileSystem_ConfigurationInterface
{
    /**
     * SFTP host.
     *
     * @var $host string
     */
    protected $host;

    /**
     * SFTP username.
     *
     * @var string
     */
    protected $user;

    /**
     * SFTP pasword.
     *
     * @var string
     */
    protected $pass;

    /**
     * SFTP start directory.
     *
     * @var string
     */
    protected $dir;

    /**
     * SFTP port number.
     *
     * @var integer
     */
    protected $port;

    /**
     * The auth type.
     *
     * @var string
     */
    protected $auth_type;

    /**
     * The path to private key file.
     *
     * @var string
     */
    protected $priv_key;

    /**
     * Path to public key file.
     *
     * @var string
     */
    protected $pub_key;

    /**
     * Passphrase for the key.
     *
     * @var string
     */
    protected $passphrase;

    /**
     * Constructs a configuration for the SFTP driver.
     *
     * @param string  $host       The host to connect to.
     * @param string  $user       The username when connecting (default = 'Anonymous').
     * @param string  $pass       The password associated with $user (default = '').
     * @param string  $dir        The directory on which to enter immediatly after connecting (default = './') (optional).
     * @param integer $port       The port to use when connecting to $host (default = 22) (optional).
     * @param string  $auth_type  Authenication type, default is "pass" other common methods are "ssh-rsa" and "ssh-dss".
     * @param string  $pub_key    Path to the public key, key must be of type $auth_type.
     * @param string  $priv_key   Path to the private key, must match $pub_key.
     * @param string  $passphrase The passphrase for the key (default = '').
     */
    public function __construct($host = 'localhost', $user = 'Anonymous', $pass = '', $dir = './', $port = 22, $auth_type = "pass", $pub_key = "", $priv_key = "", $passphrase = "")
    {
        $this->host = ($host == '' ? 'localhost' : $host);
        $this->user = $user;
        $this->pass = $pass;
        $this->dir = ($dir == '' ? './' : (substr($dir, 0, 1) == '/' || substr($dir, 0, 2) == './' ? $dir : "./$dir"));
        $this->port = ($port == "" || !is_numeric($port) ? 22 : $port);
        $this->auth_type = ($auth_type == "") ? "pass" : $auth_type;
        $this->pub_key = $pub_key;
        $this->priv_key = $priv_key;
        $this->passphrase = $passphrase;
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
     * @return int Port number.
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
     * Get the authentication method.
     *
     * @return string Authentication type.
     */
    public function getAuthType()
    {
        return $this->auth_type;
    }

    /**
     * Get the path to the public key.
     *
     * @return string Path to public key.
     */
    public function getPubKey()
    {
        return $this->pub_key;
    }

    /**
     * Get the path to the private key.
     *
     * @return string Path to private key.
     */
    public function getPrivKey()
    {
        return $this->priv_key;
    }

    /**
     * Get the passphrase associated with the key.
     *
     * @return string Passphrase.
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }
}

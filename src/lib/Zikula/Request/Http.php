<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Framework
 * @subpackage Request
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Http Request class.
 */
class Zikula_Request_Http extends Zikula_Request_Request
{
    /**
     * @var Zikula_Request_Collection
     */
    protected $get;

    /**
     * @var Zikula_Request_Collection
     */
    protected $post;

    /**
     * @var Zikula_Request_Collection
     */
    protected $files;

    /**
     * @var Zikula_Request_Collection
     */
    protected $cookie;

    /**
     * @var Zikula_Session
     */
    protected $session;

    /**
     * @var Zikula_Request_Collection
     */
    protected $server;

    /**
     * @var Zikula_Request_Collection
     */
    protected $env;

    /**
     * @var Zikula_Request_Collection
     */
    protected $args;
    
    /**
     * Initialize request object.
     *
     * @param array $options Optional overrides.
     */
    protected function initialize(array $options = array())
    {
        $this->get = isset($options['get']) ? new Zikula_Request_Collection($options['get']) : new Zikula_Request_Collection(isset($_GET) ? $_GET : array());
        $this->post = isset($options['post']) ? new Zikula_Request_Collection($options['post']) : new Zikula_Request_Collection(isset($_POST) ? $_POST : array());
        $this->files = isset($options['files']) ? new Zikula_Request_Collection($options['files']) : new Zikula_Request_Collection(isset($_FILES) ? $_FILES : array());
        $this->cookie = isset($options['cookies']) ? new Zikula_Request_Collection($options['cookies']) : new Zikula_Request_Collection(isset($_COOKIE) ? $_COOKIE : array());
        $this->server = isset($options['server']) ? new Zikula_Request_Collection($options['server']) : new Zikula_Request_Collection($_SERVER);
        $this->env = isset($options['env']) ? new Zikula_Request_Collection($options['env']) : new Zikula_Request_Collection(isset($_ENV) ? $_ENV : array());
    }

    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    public function getGet($key, $default = null)
    {
        return $this->get->get($key, $default);
    }

    public function getPost($key, $default = null)
    {
        return $this->post->get($key, $default);
    }

    public function getCookie($key, $default = null)
    {
        return $this->cookie->get($key, $default);
    }

    public function getServer($key, $default = null)
    {
        return $this->server->get($key, $default);
    }

    public function getEnv($key, $default = null)
    {
        return $this->env->get($key, $default);
    }

    public function getFiles($key, $default = null)
    {
        return $this->files->get($key, $default);
    }

    /**
     * Get session.
     *
     * @return Zikula_Session
     */
    public function getSession()
    {
        return $this->session;
    }

    public function setSession(Zikula_Session $session)
    {
        $this->session = $session;
    }

    public function isGet()
    {
        return ($this->getMethod() == 'GET') ? true : false;
    }

    public function isPost()
    {
        return ($this->getMethod() == 'POST') ? true : false;
    }

    public function isPut()
    {
        return ($this->getMethod() == 'PUT') ? true : false;
    }

    public function isDelete()
    {
        return ($this->getMethod() == 'DELETE') ? true : false;
    }

    public function isHead()
    {
        return ($this->getMethod() == 'HEAD') ? true : false;
    }

    public function isOptions()
    {
        return ($this->getMethod() == 'OPTIONS') ? true : false;
    }
}


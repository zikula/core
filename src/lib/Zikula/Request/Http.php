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
     * Container for GET.
     *
     * @var Zikula_Request_Collection
     */
    public $get;

    /**
     * Container for POST.
     *
     * @var Zikula_Request_Collection
     */
    public $post;

    /**
     * Container for FILES.
     *
     * @var Zikula_Request_Collection
     */
    public $files;

    /**
     * Container for COOKIES.
     * 
     * @var Zikula_Request_Collection
     */
    public $cookie;

    /**
     * Session object.
     *
     * @var Zikula_Session
     */
    protected $session;

    /**
     * Container for SERVER.
     *
     * @var Zikula_Request_Collection
     */
    public $server;

    /**
     * Container for ENV.
     *
     * @var Zikula_Request_Collection
     */
    public $env;

    /**
     * Container for arguments.
     *
     * @var Zikula_Request_Collection
     */
    public $args;
    
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

    /**
     * Return the request method.
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * Getter for GET.
     *
     * @param string $key     Key to get.
     * @param string $default Default if not found.
     *
     * @return mixed
     */
    public function getGet($key, $default = null)
    {
        return $this->get->get($key, $default);
    }

    /**
     * Getter for POST.
     *
     * @param string $key     Key to get.
     * @param string $default Default if not found.
     *
     * @return mixed
     */
    public function getPost($key, $default = null)
    {
        return $this->post->get($key, $default);
    }

    /**
     * Getter for COOKIE.
     *
     * @param string $key     Key to get.
     * @param string $default Default if not found.
     *
     * @return mixed
     */
    public function getCookie($key, $default = null)
    {
        return $this->cookie->get($key, $default);
    }

    /**
     * Getter for SERVER.
     *
     * @param string $key     Key to get.
     * @param string $default Default if not found.
     *
     * @return mixed
     */
    public function getServer($key, $default = null)
    {
        return $this->server->get($key, $default);
    }

    /**
     * Getter for ENV.
     *
     * @param string $key     Key to get.
     * @param string $default Default if not found.
     *
     * @return mixed
     */
    public function getEnv($key, $default = null)
    {
        return $this->env->get($key, $default);
    }

    /**
     * Getter for FILES.
     *
     * @param string $key     Key to get.
     * @param string $default Default if not found.
     *
     * @return mixed
     */
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

    /**
     * Set session.
     *
     * @param Zikula_Session $session
     *
     * @return void
     */
    public function setSession(Zikula_Session $session)
    {
        $this->session = $session;
    }

    /**
     * Is this method a GET.
     *
     * @return boolean
     */
    public function isGet()
    {
        return ($this->getMethod() == 'GET') ? true : false;
    }

    /**
     * Is this method a POST.
     *
     * @return boolean
     */
    public function isPost()
    {
        return ($this->getMethod() == 'POST') ? true : false;
    }

    /**
     * Is this method a PUT.
     *
     * @return boolean
     */
    public function isPut()
    {
        return ($this->getMethod() == 'PUT') ? true : false;
    }

    /**
     * Is this method a DELETE.
     *
     * @return boolean
     */
    public function isDelete()
    {
        return ($this->getMethod() == 'DELETE') ? true : false;
    }

    /**
     * Is this method a HEAD.
     *
     * @return boolean
     */
    public function isHead()
    {
        return ($this->getMethod() == 'HEAD') ? true : false;
    }

    /**
     * Is this method a OPTIONS.
     *
     * @return boolean
     */
    public function isOptions()
    {
        return ($this->getMethod() == 'OPTIONS') ? true : false;
    }
}


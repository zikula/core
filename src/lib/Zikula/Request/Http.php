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
class Zikula_Request_Http extends Zikula_Request_AbstractRequest
{
    /**
     * Container for GET.
     *
     * @var Zikula_Request_Collection
     */
    public $query;

    /**
     * Container for POST.
     *
     * @var Zikula_Request_Collection
     */
    public $request;

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
    protected $env;

    /**
     * Container for arguments.
     *
     * @var Zikula_Request_Collection
     */
    protected $args;

    /**
     * Initialize request object.
     *
     * @param array $options Optional overrides.
     *
     * @return void
     */
    // needs to be public for temporary workaround for short urls in SystemListeners::setupRequest
    public /*protected */function initialize(array $options = array())
    {
        $this->query = new Zikula_Request_Collection(isset($options['get']) ? $options['get'] : $_GET);
        $this->request = new Zikula_Request_Collection(isset($options['post']) ? $options['post'] : $_POST);
        //$this->request = new Zikula_Request_Collection(isset($options['request']) ? $options['request'] : $_POST);
        $this->files = new Zikula_Request_Collection(isset($options['files']) ? $options['files'] : $_FILES);
        $this->cookie = new Zikula_Request_Collection(isset($options['cookie']) ? $options['cookie'] : $_COOKIE);
        $this->server = new Zikula_Request_Collection(isset($options['server']) ? $options['server'] : $_SERVER);
        $this->env = new Zikula_Request_Collection(isset($options['env']) ? $options['env'] : $_ENV);
    }

    /**
     * Return the request method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->server->get('REQUEST_METHOD');
    }

    /**
     * Getter for GET.
     *
     * @deprecated use $request->query instead
     *
     * @return Zikula_Request_Collection
     */
    public function getGet()
    {
        LogUtil::log('Zikula_Request_Http->getGet() is deprecated, please use $request->query', E_USER_DEPRECATED);

        return $this->query;
    }

    /**
     * Getter for POST.
     *
     * @deprecated use $request->request instead
     *
     * @return Zikula_Request_Collection
     */
    public function getPost()
    {
        LogUtil::log('Zikula_Request_Http->getPost() is deprecated, please use $request->request', E_USER_DEPRECATED);

        return $this->request;
    }

    /**
     * Getter for COOKIE.
     *
     * @return Zikula_Request_Collection
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * Getter for SERVER.
     *
     * @deprecated use $request->server instead
     *
     * @return Zikula_Request_Collection
     */
    public function getServer()
    {
        LogUtil::log('Zikula_Request_Http->getServer() is deprecated, please use $request->server', E_USER_DEPRECATED);

        return $this->server;
    }

    /**
     * Getter for ENV.
     *
     * @return Zikula_Request_Collection
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Getter for args.
     *
     * @return Zikula_Request_Collection
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Getter for FILES.
     *
     * @deprecated use $request->files instead
     *
     * @return Zikula_Request_Collection
     */
    public function getFiles()
    {
        LogUtil::log('Zikula_Request_Http->getFiles() is deprecated, please use $request->files', E_USER_DEPRECATED);

        return $this->files;
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
     * @param Zikula_Session $session Session to set.
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


<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Http Request class.
 *
 * @deprecated
 */
class Zikula_Request_Http extends Zikula_Request_AbstractRequest
{
    public $files;

    /**
     * Constructor.
     *
     * @param array  $query      The GET parameters
     * @param array  $request    The POST parameters
     * @param array  $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array  $cookies    The COOKIE parameters
     * @param array  $files      The FILES parameters
     * @param array  $server     The SERVER parameters
     * @param string $content    The raw body data
     *
     * @api
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->query = new Zikula_Bag_ParameterBag($query);
        $this->request = new Zikula_Bag_ParameterBag($request);
        $this->files = new Zikula_Bag_FileBag($files);
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
     * @return \Symfony\Component\HttpFoundation\ParameterBag
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
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getPost()
    {
        LogUtil::log('Zikula_Request_Http->getPost() is deprecated, please use $request->request', E_USER_DEPRECATED);

        return $this->request;
    }

    /**
     * Getter for COOKIE.
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getCookie()
    {
        return $this->cookies;
    }

    /**
     * Getter for SERVER.
     *
     * @deprecated use $request->server instead
     *
     * @return \Symfony\Component\HttpFoundation\ServerBag
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
        // this is not defined!
        //return $this->env;
        return null;
    }

    /**
     * Getter for args.
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getArgs()
    {
        return $this->attributes->all();
    }

    /**
     * Getter for FILES.
     *
     * @deprecated use $request->files instead
     *
     * @return \Symfony\Component\HttpFoundation\FileBag
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
     * @param Zikula_Session $session Session to set
     *
     * @return void
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Is this method a GET.
     *
     * @deprecated since 1.4.0
     * @see Request::isMethod('GET')
     *
     * @return boolean
     */
    public function isGet()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod(\'GET\') instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('GET');
    }

    /**
     * Is this method a POST.
     *
     * @deprecated since 1.4.0
     * @see Request::isMethod('POST')
     *
     * @return boolean
     */
    public function isPost()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod(\'POST\') instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('POST');
    }

    /**
     * Is this method a PUT.
     *
     * @deprecated since 1.4.0
     * @see Request::isMethod('PUT')
     *
     * @return boolean
     */
    public function isPut()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod(\'PUT\') instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('PUT');
    }

    /**
     * Is this method a DELETE.
     *
     * @deprecated since 1.4.0
     * @see Request::isMethod('DELETE')
     *
     * @return boolean
     */
    public function isDelete()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod(\'DELETE\') instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('DELETE');
    }

    /**
     * Is this method a HEAD.
     *
     * @deprecated since 1.4.0
     * @see Request::isMethod('HEAD')
     *
     * @return boolean
     */
    public function isHead()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod(\'HEAD\') instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('HEAD');
    }

    /**
     * Is this method a OPTIONS.
     *
     * @deprecated since 1.4.0
     * @see Request::isMethod('OPTIONS')
     *
     * @return boolean
     */
    public function isOptions()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod(\'OPTIONS\') instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('OPTIONS');
    }
}

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

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Http Request class.
 */
class Zikula_Request_Http extends Zikula_Request_AbstractRequest
{
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
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Is this method a GET.
     *
     * @deprecated since 1.3.6
     * @use Request::isMethod('GET')
     *
     * @return boolean
     */
    public function isGet()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod() instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('GET');
    }

    /**
     * Is this method a POST.
     *
     * @deprecated since 1.3.6
     * @use Request::isMethod('POST')
     *
     * @return boolean
     */
    public function isPost()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod() instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('POST');
    }

    /**
     * Is this method a PUT.
     *
     * @deprecated since 1.3.6
     * @use Request::isMethod('PUT')
     *
     * @return boolean
     */
    public function isPut()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod() instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('PUT');
    }

    /**
     * Is this method a DELETE.
     *
     * @deprecated since 1.3.6
     * @use Request::isMethod('DELETE')
     *
     * @return boolean
     */
    public function isDelete()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod() instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('DELETE');
    }

    /**
     * Is this method a HEAD.
     *
     * @deprecated since 1.3.6
     * @use Request::isMethod('HEAD')
     *
     * @return boolean
     */
    public function isHead()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod() instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('HEAD');
    }

    /**
     * Is this method a OPTIONS.
     *
     * @deprecated since 1.3.6
     * @use Request::isMethod('OPTIONS')
     *
     * @return boolean
     */
    public function isOptions()
    {
        LogUtil::log(sprintf('%s is deprecated use isMethod() instead', __METHOD__), E_USER_DEPRECATED);

        return $this->isMethod('OPTIONS');
    }
}


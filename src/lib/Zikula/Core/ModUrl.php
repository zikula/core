<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core;

/**
 * Url class.
 *
 * @deprecated as of Core 1.4.0 to be removed in Core 2.0.0
 * instead, utilize `UrlInterface` for all typehints and use RouteUrl where possible
 */
class ModUrl implements UrlInterface
{
    private $application;
    private $controller;
    private $action;
    private $args;
    private $language;
    private $fragment;

    public function __construct($application, $controller, $action, $language, array $args = array(), $fragment = null)
    {
        $this->application = $application;
        $this->controller = $controller;
        $this->action = $action;
        $this->args = $args;
        $this->language = $language;
        $this->fragment = $fragment;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function getUrl($ssl = null, $fqurl = null, $forcelongurl = false, $forcelang = false)
    {
        return \ModUtil::url($this->application, $this->controller, $this->action, $this->args, $ssl, $this->fragment, $fqurl, $forcelongurl, $forcelang);
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }

    public function toArray()
    {
        return array(
            'application' => $this->application,
            'controller' => $this->controller,
            'action' => $this->action,
            'args' => $this->args,
            'language' => $this->language,
            'fragment' => $this->fragment);
    }
}

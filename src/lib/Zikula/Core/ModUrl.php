<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package XXXX
 * @subpackage XXXX
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core;

use Symfony\Component\Routing\RouterInterface;
use ZLanguage;

/**
 * Url class.
 */
class ModUrl
{
    private $application;
    private $controller;
    private $action;
    private $route;
    private $args;
    private $language;
    private $fragment;

    public function __construct($application = null, $controller = null, $action = null, $language = null, array $args=array(), $fragment=null)
    {
        $this->application = $application;
        $this->controller = $controller;
        $this->action = $action;
        $this->args = $args;
        $language = (empty($language)) ? ZLanguage::getLanguageCode() : $language;
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
        if (!empty($this->language)) {
            return $this->language;
        } else if (!empty($this->args['_locale'])) {
            return $this->args['_locale'];
        }
        return null;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function getUrl($ssl = null, $fqurl = null, $forcelongurl = false, $forcelang=false)
    {
        if (!empty($this->route)) {
            $fqurl = (is_bool($fqurl) && $fqurl) ? RouterInterface::ABSOLUTE_URL : RouterInterface::ABSOLUTE_PATH;
            $fragment =  (!empty($this->fragment)) ? '#' . $this->fragment : '';

            return \ServiceUtil::get('router')->generate($this->route, $this->args, $fqurl) . $fragment;
        } else {

            return \ModUtil::url($this->application, $this->controller, $this->action, $this->args, $ssl, $this->fragment, $fqurl, $forcelongurl, $forcelang);
        }
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route, $args = array())
    {
        $this->route = $route;
        $this->args = $args;
    }

    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Factory method to create instance and set Route simultaneously
     *
     * @param $route
     * @param array $args
     *
     * @return ModUrl
     *
     * @throws \InvalidArgumentException
     */
    public static function fromRoute($route, $args = array())
    {
        if (empty($route)) {
            throw new \InvalidArgumentException();
        }
        $modUrl = new self();
        $modUrl->setRoute($route, $args);

        return $modUrl;
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
            'fragment' => $this->fragment,
            'route' => $this->route,
        );
    }
}
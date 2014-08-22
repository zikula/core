<?php
/**
 * Copyright 2014 Zikula Foundation
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

use Symfony\Component\Routing\RouterInterface;

/**
 * RouteUrl class.
 *
 * @TODO in Core 2.0.0 remove `extends ModUrl` and simply leave `implements UrlInterface`
 */
class RouteUrl extends ModUrl implements UrlInterface
{
    private $route;
    private $args;
    private $fragment;

    public function __construct($route, array $args=array(), $fragment=null)
    {
        $this->route = $route;
        $this->args = $args;
        $this->fragment = $fragment;
    }

    public function getLanguage()
    {
        return $this->args['_locale'];
    }

    public function setLanguage($lang)
    {
        $this->args['_locale'] = $lang;
    }

    public function getUrl($ssl = null, $fqUrl = null)
    {
        $router = \ServiceUtil::get('router');
        $fqUrl = (is_bool($fqUrl) && $fqUrl) ? RouterInterface::ABSOLUTE_URL : RouterInterface::ABSOLUTE_PATH;
        $fragment =  (!empty($this->fragment)) ? '#' . $this->fragment : '';

        $oldScheme = $router->getContext()->getScheme();
        if ($ssl) {
            $router->getContext()->setScheme('https');
        }
        $url = $router->generate($this->route, $this->args, $fqUrl) . $fragment;
        if ($ssl) {
            $router->getContext()->setScheme($oldScheme);
        }

        return $url;
    }

    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Factory method to create instance and set Route simultaneously
     *
     * @param string $route
     * @param array $args
     * @param string $fragment
     *
     * @return ModUrl
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromRoute($route, $args = array(), $fragment = '')
    {
        if (empty($route)) {
            throw new \InvalidArgumentException();
        }
        $routeUrl = new self($route, $args, $fragment);

        return $routeUrl;
    }

    public function toArray()
    {
        return array(
            'route' => $this->route,
            'args' => $this->args,
            'fragment' => $this->fragment);
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }

    public function getFragment()
    {
        return $this->fragment;
    }
}
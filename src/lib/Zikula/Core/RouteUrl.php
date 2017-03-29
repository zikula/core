<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

    public function __construct($route, array $args = [], $fragment = null)
    {
        $this->route = $route;
        $this->args = $args;
        $this->fragment = $fragment;
    }

    public function getLanguage()
    {
        return isset($this->args['_locale']) ? $this->args['_locale'] : null;
    }

    public function setLanguage($lang)
    {
        $this->args['_locale'] = $lang;
    }

    /**
     * @deprecated remove at Core-2.0 and generate url from properties
     * @param null $ssl
     * @param null $fqUrl
     * @param bool $forcelongurl
     * @param bool $forcelang
     * @return string
     */
    public function getUrl($ssl = null, $fqUrl = null, $forcelongurl = false, $forcelang = false)
    {
        $router = \ServiceUtil::get('router');
        $fqUrl = (is_bool($fqUrl) && $fqUrl) ? RouterInterface::ABSOLUTE_URL : RouterInterface::ABSOLUTE_PATH;
        $fragment =  (!empty($this->fragment)) ? '#' . $this->fragment : '';

        $oldScheme = $router->getContext()->getScheme();
        if ($ssl) {
            $router->getContext()->setScheme('https');
        }
        try {
            $url = $router->generate($this->route, $this->args, $fqUrl) . $fragment;
        } catch (\Exception $e) {
            return '';
        }
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
     * @return RouteUrl
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromRoute($route, $args = [], $fragment = '')
    {
        if (empty($route)) {
            throw new \InvalidArgumentException();
        }
        $routeUrl = new self($route, $args, $fragment);

        return $routeUrl;
    }

    public function toArray()
    {
        return [
            'route' => $this->route,
            'args' => $this->args,
            'fragment' => $this->fragment
        ];
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

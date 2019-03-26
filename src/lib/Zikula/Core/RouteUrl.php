<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

/**
 * RouteUrl class.
 */
class RouteUrl implements UrlInterface
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

<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Routing;

use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteProvider.
 */
class RouteProvider implements RouteProviderInterface
{
    private $path;

    private $prefix;

    private $class;

    private $routeCollection;

    public function setCachePath($path, $prefix, $class)
    {
        $this->path = $path;
        $this->prefix = $prefix;
        $this->class = $class;
    }

    private function getRoute($name)
    {
        if (!isset($this->routeCollection)) {
            $this->getRoutes();
        }

        return $this->routeCollection->get($name);
    }

    public function getRoutes()
    {
        if (isset($this->routeCollection)) {
            return $this->routeCollection;
        }

        $file = "{$this->path}/{$this->prefix}{$this->class}.php";
        if (file_exists($file)) {
            require_once($file);
            $class = new $this->class();
            $this->routeCollection = $class->getRoutes();
        } else {
            $this->routeCollection = new RouteCollection();
        }

        return $this->routeCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        return $this->getRoutes();
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteByName($name)
    {
        return $this->getRoute($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesByNames($names)
    {
        $routes = array();

        if (empty($names)) {
            return $routes;
        }
        foreach ($names as $name) {
            try {
                $routes[$name] = $this->getRouteByName($name);
            } catch (RouteNotFoundException $e) {

            }
        }

        return $routes;
    }
}
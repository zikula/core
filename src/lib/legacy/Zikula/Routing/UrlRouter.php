<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Url router base class.
 *
 * @deprecated
 */
class Zikula_Routing_UrlRouter
{
    /**
     * The list of managed routes (Zikula_Routing_UrlRoute instances).
     *
     * @var array
     */
    protected $routes;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->routes = array();
    }

    /**
     * Generate a short url for given arguments.
     *
     * @param string $name   Optional name of route to be used (if not set the route will be selected based on given params).
     * @param array  $params The arguments to be processed by the created url.
     *
     * @return mixed string With created url or false on error.
     */
    public function generate($name = '', array $params = array())
    {
        // reference to the route used for url creation
        $route = null;

        if ($name) {
            // a certain route should be used
            if (!isset($this->routes[$name])) {
                // this route does not exist, so we abort here
                return false;
            }
            // use this route
            $route = $this->routes[$name];
        } else {
            // determine the route based on given params
            foreach ($this->routes as $testRoute) {
                if (!$testRoute->matchParameters($params)) {
                    // this route does not fit to our arguments, so we skip it
                    continue;
                }

                // use this route
                $route = $testRoute;

                // exit loop as the first match wins
                break;
            }

            // abort if we did not found a route to use
            if (is_null($route)) {
                return false;
            }
        }

        // let the route do the actual url creation
        $url = $route->generate($params);

        // return the result
        return $url;
    }

    /**
     * Parse a given url and return the params read out of it.
     *
     * @param string $url The input url.
     *
     * @throws InvalidArgumentException If the Url is empty.
     *
     * @return mixed array With determined params or false on error.
     */
    public function parse($url = '')
    {
        // return if we have an empty input
        if (empty($url)) {
            throw new InvalidArgumentException('Zikula_Routing_UrlRouter->parse: $url was empty!');
        }

        // reference to resulting params
        $params = null;

        // search for the right route for given url
        foreach ($this->routes as $testRoute) {
            // check if this route does the job
            $testParams = $testRoute->matchesUrl($url);
            // if not...
            if ($testParams === false) {
                // skip it
                continue;
            }

            // store parameters
            $params = $testParams;

            // exit loop as the first match wins
            break;
        }

        // if we found something return it
        if (!is_null($params)) {
            return $params;
        }

        // else return false
        return false;
    }

    /**
     * Set (or add) a certain route to this router instance.
     *
     * @param string                  $name  Storage name for the route.
     * @param Zikula_Routing_UrlRoute $route The actual route instance.
     *
     * @throws InvalidArgumentException If name is empty.
     *
     * @return void
     */
    public function set($name, Zikula_Routing_UrlRoute $route)
    {
        // return if we have an empty input
        if (empty($name)) {
            throw new InvalidArgumentException('Zikula_Routing_UrlRouter->set: $name was empty!');
        }

        if (is_null($route)) {
            throw new InvalidArgumentException('Zikula_Routing_UrlRouter->set: $route was null!');
        }

        // store given route
        $this->routes[$name] = $route;
    }
}

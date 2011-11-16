<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Matcher;

use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
 * TraceableUrlMatcher helps debug path info matching by tracing the match.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TraceableUrlMatcher extends UrlMatcher
{
    const ROUTE_DOES_NOT_MATCH = 0;
    const ROUTE_ALMOST_MATCHES = 1;
    const ROUTE_MATCHES        = 2;

    protected $traces;

    public function getTraces($pathinfo)
    {
        $this->traces = array();

        try {
            $this->match($pathinfo);
        } catch (ExceptionInterface $e) {
        }

        return $this->traces;
    }

    protected function matchCollection($pathinfo, RouteCollection $routes)
    {
        $pathinfo = urldecode($pathinfo);

        foreach ($routes as $name => $route) {
            if ($route instanceof RouteCollection) {
                if (!$ret = $this->matchCollection($pathinfo, $route)) {
                    continue;
                }

                return true;
            }

            $compiledRoute = $route->compile();

            if (!preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
                // does it match without any requirements?
                $r = new Route($route->getPattern(), $route->getDefaults(), array(), $route->getOptions());
                $cr = $r->compile();
                if (!preg_match($cr->getRegex(), $pathinfo)) {
                    $this->addTrace(sprintf('Pattern "%s" does not match', $route->getPattern()), self::ROUTE_DOES_NOT_MATCH, $name, $route);

                    continue;
                }

                foreach ($route->getRequirements() as $n => $regex) {
                    $r = new Route($route->getPattern(), $route->getDefaults(), array($n => $regex), $route->getOptions());
                    $cr = $r->compile();

                    if (in_array($n, $cr->getVariables()) && !preg_match($cr->getRegex(), $pathinfo)) {
                        $this->addTrace(sprintf('Requirement for "%s" does not match (%s)', $n, $regex), self::ROUTE_ALMOST_MATCHES, $name, $route);

                        continue;
                    }
                }

                continue;
            }

            // check HTTP method requirement
            if ($req = $route->getRequirement('_method')) {
                // HEAD and GET are equivalent as per RFC
                if ('HEAD' === $method = $this->context->getMethod()) {
                    $method = 'GET';
                }

                if (!in_array($method, $req = explode('|', strtoupper($req)))) {
                    $this->allow = array_merge($this->allow, $req);

                    $this->addTrace(sprintf('Method "%s" does not match the requirement ("%s")', $this->context->getMethod(), implode(', ', $req)), self::ROUTE_ALMOST_MATCHES, $name, $route);

                    continue;
                }
            }

            // check HTTP scheme requirement
            if ($scheme = $route->getRequirement('_scheme')) {
                if ($this->context->getScheme() !== $scheme) {
                    $this->addTrace(sprintf('Scheme "%s" does not match the requirement ("%s"); the user will be redirected', $this->context->getScheme(), $scheme), self::ROUTE_ALMOST_MATCHES, $name, $route);

                    return true;
                }
            }

            $this->addTrace('Route matches!', self::ROUTE_MATCHES, $name, $route);

            return true;
        }
    }

    private function addTrace($log, $level = self::ROUTE_DOES_NOT_MATCH, $name = null, $route = null)
    {
        $this->traces[] = array(
            'log'     => $log,
            'name'    => $name,
            'level'   => $level,
            'pattern' => null !== $route ? $route->getPattern() : null,
        );
    }
}

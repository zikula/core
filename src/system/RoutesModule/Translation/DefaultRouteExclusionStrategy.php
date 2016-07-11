<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\RoutesModule\Translation;

use JMS\I18nRoutingBundle\Router\DefaultRouteExclusionStrategy as BaseDefaultRouteExclusionStrategy;
use Symfony\Component\Routing\Route;


/**
 * Class DefaultRouteExclusionStrategy.
 */
class DefaultRouteExclusionStrategy extends BaseDefaultRouteExclusionStrategy
{
    public function shouldExcludeRoute($routeName, Route $route)
    {
        $exclude = parent::shouldExcludeRoute($routeName, $route);

        $module = $route->getDefault('_zkModule');
        if (!$exclude && $module !== null && isset($GLOBALS['translation_extract_routes_bundle'])) {
            return $module !== $GLOBALS['translation_extract_routes_bundle'];
        }

        return $exclude;
    }
}

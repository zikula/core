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

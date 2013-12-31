<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
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

namespace Zikula\Bundle\CoreBundle\Routing\Matcher;

use Symfony\Cmf\Component\Routing\NestedMatcher\UrlMatcher as BaseUrlMatcher;
use Symfony\Component\Routing\Route;

class UrlMatcher extends BaseUrlMatcher
{
    /**
     * {@inheritdoc}
     */
    protected function getAttributes(Route $route, $name, array $attributes)
    {
        $attributes = parent::getAttributes($route, $name, $attributes);
        $attributes['_module'] = $route->getOption('module');
        $attributes['_type'] = $route->getOption('type');
        $attributes['_func'] = $route->getOption('func');

        return $attributes;
    }
}

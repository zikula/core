<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Helper;

use Symfony\Component\DependencyInjection\Container;

class ServiceNameHelper
{
    /**
     * Convert a class name to a standardized symfony service name
     *   'Acme\FooBundle\Bar\FooBar' => 'acme.foo_bundle.bar.foo_bar'
     * @param $classname
     * @return string
     */
    public function generateServiceNameFromClassName($classname)
    {
        $classname = str_replace(['\\', '_'], '.', $classname); // @todo in Core-2.0 the '_' can be removed.
        $classname = Container::underscore($classname);

        return trim($classname, "\\_. \t\n\r\0\x0B");
    }
}
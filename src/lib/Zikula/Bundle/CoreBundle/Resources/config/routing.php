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

use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\DependencyInjection\Loader;

/** @var ZikulaKernel $kernel */
$kernel = $container->get('kernel');
$bundles = $kernel->getModules();
$bundles = array_merge($bundles, $kernel->getThemes());

$collection = new RouteCollection();

/** @var Zikula\Core\AbstractBundle $bundle */
foreach ($bundles as $bundle) {
    try {
        $collection->addCollection(
            /** @var \Symfony\Component\Routing\Loader\PhpFileLoader $loader */
            $loader->import($module->getRoutingConfig()),
            '/'
        );
    } catch (FileLoaderLoadException $e) {
        // Fail silently if routing config file does not exist.
    }
}
return $collection;
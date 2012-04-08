<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

// Check PHP version
$x = explode('.', str_replace('-', '.', phpversion()));
$phpVersion = "$x[0].$x[1].$x[2]";
if (version_compare($phpVersion, '5.3.2', '>=') == false) {
    die("Zikula requires PHP version 5.3.2 or greater. Your server is using version $phpVersion.");
}

include __DIR__.'/../vendor/.composer/autoload.php';
include __DIR__.'/ZLoader.php';
ZLoader::register();

$core = new Zikula\Core\Core(__DIR__.'/Resources/config/core.xml');
$core->boot();

// Load system configuration
$event = new Zikula\Core\Event\GenericEvent($core);
$core->getDispatcher()->dispatch('bootstrap.getconfig', $event);

$event = new Zikula\Core\Event\GenericEvent($core);
$core->getDispatcher()->dispatch('bootstrap.custom', $event);

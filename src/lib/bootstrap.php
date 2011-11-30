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

use Zikula\Core\Core;
use Zikula\Core\Event\GenericEvent;

// Check PHP version
$x = explode('.', str_replace('-', '.', phpversion()));
$phpVersion = "$x[0].$x[1].$x[2]";
if (version_compare($phpVersion, '5.3.2', '>=') == false) {
    die("Zikula requires PHP version 5.3.2 or greater. Your server is using version $phpVersion.");
}

include 'lib/ZLoader.php';
ZLoader::register();

$core = new Core();
$core->boot();

// Load system configuration
$event = new GenericEvent('bootstrap.getconfig', $core);
$core->getEventManager()->notify($event);

$event = new GenericEvent('bootstrap.custom', $core);
$core->getEventManager()->notify($event);

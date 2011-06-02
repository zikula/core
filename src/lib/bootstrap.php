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
// Please note this version check must occur here and not in the internals of Zikula
// because of syntax changes in PHP 5.3, the version checks may never be reached since
// PHP will just bomb out with syntax errors.  There is no translation available at
// this point either.
$x = explode('.', str_replace('-', '.', phpversion()));
$phpVersion = "$x[0].$x[1].$x[2]";
if (version_compare($phpVersion, '5.3.2', '>=') == false) {
    die("Zikula requires PHP version 5.3.2 or greater. Your server is using version $phpVersion.");
}

include 'lib/ZLoader.php';
ZLoader::register();

$core = new Zikula_Core();
$core->boot();

// Load system configuration
$event = new Zikula_Event('bootstrap.getconfig', $core);
$core->getEventManager()->notify($event);

$event = new Zikula_Event('bootstrap.custom', $core);
$core->getEventManager()->notify($event);

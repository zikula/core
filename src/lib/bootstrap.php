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
include 'lib/ZLoader.php';
ZLoader::register();

$core = new Zikula_Core();
$core->boot();

// Load system configuration
$event = new Zikula_Event('bootstrap.getconfig', $core);
$core->getEventManager()->notify($event);

$event = new Zikula_Event('bootstrap.custom', $core);
$core->getEventManager()->notify($event);

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

include __DIR__.'/autoload.php';
include __DIR__.'/../src/ZLoader.php';
ZLoader::register();

$core = new Zikula\Core\Core(__DIR__.'/../src/Resources/config/core.xml', __DIR__.'/../src/EventHandlers');
$core->boot();

// Load system configuration
$event = new Zikula\Core\Event\GenericEvent($core);
$core->getDispatcher()->dispatch('bootstrap.getconfig', $event);

$event = new Zikula\Core\Event\GenericEvent($core);
$core->getDispatcher()->dispatch('bootstrap.custom', $event);

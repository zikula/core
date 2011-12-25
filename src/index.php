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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Zikula\Framework\ControllerResolver;
use Zikula\Core\Event\GenericEvent;

include 'lib/bootstrap.php';
$core->init();

$core->getEventManager()->notify(new GenericEvent('frontcontroller.predispatch'));

$request = Request::createFromGlobals();

$core->getEventManager()->addSubscriber(new Zikula\Core\Listener\ThemeListener());

$resolver = new ControllerResolver();

$kernel = new HttpKernel($core->getEventManager(), $resolver);
$kernel->handle($request)->send();


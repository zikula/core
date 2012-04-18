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
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zikula\Framework\ControllerResolver;
use Zikula\Core\Event\GenericEvent;

require_once __DIR__.'/../app/bootstrap.php';

$kernel = new AppKernel('dev', true);
//$kernel->loadClassCache();
$kernel->boot();

// @todo temporary hack
$GLOBALS['ZConfig'] = $kernel->getContainer()->getParameter('_zconfig');

$core = $kernel->getContainer()->get('zikula');//new Zikula\Core\Core($kernel->getContainer());
$core->boot();
$core->init();

$core->getDispatcher()->dispatch('frontcontroller.predispatch', new GenericEvent());

$request = $kernel->getContainer()->get('request');

$response = $kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
$response->send();
$kernel->terminate($request, $response);

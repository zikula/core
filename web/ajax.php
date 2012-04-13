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
use Zikula\Framework\AjaxControllerResolver;

include __DIR__.'/../app/bootstrap.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$kernel->boot();

$core = new Zikula\Core\Core($kernel->getContainer());
$core->boot();
$core->init(Zikula_Core::STAGE_ALL | Zikula_Core::STAGE_AJAX & ~Zikula_Core::STAGE_DECODEURLS);

$request = $core->getContainer()->get('request');
$resolver = new AjaxControllerResolver();

$kernel = new HttpKernel($core->getDispatcher(), $resolver);
$response = $kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
$response->send();
$kernel->terminate($request, $response);

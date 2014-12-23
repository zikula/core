<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Zikula_Request_Http as Request;

include 'lib/bootstrap.php';

$request = Request::createFromGlobals();
$disableCore = $request->get('disable_core', null);
if (!isset($disableCore) || ($disableCore === false)) {
    $core->init(Zikula_Core::STAGE_ALL, $request);

    // this event for BC only. remove in 2.0.0
    $core->getDispatcher()->dispatch('frontcontroller.predispatch', new \Zikula\Core\Event\GenericEvent());
} else {
    System::setInstalling(true);
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

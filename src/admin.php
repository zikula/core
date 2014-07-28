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

use Zikula_Request_Http as Request;

include 'lib/bootstrap.php';
$request = Request::createFromGlobals();
$core->getContainer()->set('request', $request);
$core->init(Zikula_Core::STAGE_ALL, $request);
System::redirect(ModUtil::url('ZikulaAdminModule', 'admin', 'adminpanel'));

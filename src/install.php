<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Installer
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Zikula_Request_Http as Request;

$warnings = array();
if (ini_set('memory_limit', '128M') === false) {
    $currentSetting = ini_get('memory_limit');
    $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The upgrade process may fail at your current setting of %4$s', array('ini_set', 'memory_limit', '128M', $currentSetting));
}
if (ini_set('max_execution_time', 86400) === false) {
    $currentSetting = ini_get('max_execution_time');
    $warnings[] = __f('Could not use %1$s to set the %2$s to the value of %3$s. The upgrade process may fail at your current setting of %4$s', array('ini_set', 'max_execution_time', '86400', $currentSetting));
}

include 'lib/bootstrap.php';
include 'install/lib.php';

$request = Request::createFromGlobals();
install($core, $request, $warnings);

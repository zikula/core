<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

include 'lib/ZLoader.php';
ZLoader::register();


// start PN
pnInit();

if (SessionUtil::hasExpired()) {
    // Session has expired, display warning
    header('HTTP/1.0 403 Access Denied');
    echo pnModAPIFunc('Users', 'user', 'expiredsession');
    Theme::getInstance()->themefooter();
    pnShutDown();
}

// Get module
$module = FormUtil::getPassedValue('module', '', 'GETPOST');

if (empty($module)) {
    // call for admin.php without module parameter
    if (!pnUserLoggedIn()) {
        pnRedirect(ModUtil::url('Users', 'user', 'loginscreen', array(
            'returnpage'    => urlencode(ModUtil::url('Admin', 'admin', 'adminpanel'))
        )));
    } else {
        pnRedirect(ModUtil::url('Admin', 'admin', 'adminpanel'));
    }
    pnShutDown();
} else if (!pnModAvailable($module) || !SecurityUtil::checkPermission("$module::", '::', ACCESS_EDIT)) {
    // call for an unavailable module - either not available or not authorized
    header('HTTP/1.0 403 Access Denied');
    echo 'Module <strong>' . DataUtil::formatForDisplay($module) . '</strong> not available';
    Theme::getInstance()->themefooter();
    pnShutDown();
}

// get the module information
$modinfo = ModUtil::getInfo(ModUtil::getIdFromName($module));

if ($modinfo['type'] == 2 || $modinfo['type'] == 3) {
    // Redirect to new style admin panel
    pnRedirect(ModUtil::url($module, 'admin'));
    pnShutDown();
}



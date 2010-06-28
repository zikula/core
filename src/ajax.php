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
System::init(System::CORE_STAGES_ALL & ~System::CORE_STAGES_DECODEURLS | CORE_STAGES_AJAX);

// Get variables
$module = FormUtil::getPassedValue('module', '', 'GETPOST');
$type   = FormUtil::getPassedValue('type', 'ajax', 'GETPOST');
$func   = FormUtil::getPassedValue('func', '', 'GETPOST');

// Check for site closed
if (System::getVar('siteoff') && !SecurityUtil::checkPermission('Settings::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'Users' && $func == 'siteofflogin')) {
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW) && UserUtil::isLoggedIn()) {
        UserUtil::logout();
    }
    AjaxUtil::error(__('The site is currently off-line.'));
}

if (empty($func)) {
    AjaxUtil::error(__f("Missing parameter '%s'", 'func'));
}

// get module information
$modinfo = ModUtil::getInfoFromName($module);
if ($modinfo == false) {
    AjaxUtil::error(__f("Error! The '%s' module is unknown.", DataUtil::formatForDisplay($module)));
}

if (!ModUtil::available($modinfo['name'])) {
    AjaxUtil::error(__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($module)));
}

$arguments = array(); // this is entirely ununsed? - drak

if (ModUtil::load($modinfo['name'], $type)) {
    if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
        $dbConn = DBConnectionStack::getConnection();
        $dbConn->beginTransaction();
    }

    // Run the function
    try {
        $return = ModUtil::func($modinfo['name'], $type, $func, $arguments);
    } catch (Exception $e) {
        $return = false;
    }

    if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
        if ($return === false && $e instanceof PDOException) {
            $return = __('Error! The transaction failed. Performing rollback.') . "\n" . $return;
            $dbConn->rollback();
            AjaxUtil::error($return);
            $return == true;
        }
        $dbConn->commit();
    }
} else {
    $return = false;
}

// Sort out return of function.  Can be
// true - finished
// false - display error msg
// text - return information
if ($return === true) {
    // Nothing to do here everything was done in the module
} elseif ($return === false) {
    // Failed to load the module
    AjaxUtil::error(__f("Could not load the '%s' module (at '%s' function).", array(DataUtil::formatForDisplay($module), DataUtil::formatForDisplay($func))));
} else {
    AjaxUtil::output($return, true, false);
}

System::shutdown();

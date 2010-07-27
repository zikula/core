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

include 'lib/bootstrap.php';
$core->init(System::STAGES_ALL | System::STAGES_AJAX & ~System::STAGES_DECODEURLS);

// Get variables
$module = FormUtil::getPassedValue('module', '', 'GETPOST', FILTER_SANITIZE_STRING);
$type   = FormUtil::getPassedValue('type', 'ajax', 'GETPOST', FILTER_SANITIZE_STRING);
$func   = FormUtil::getPassedValue('func', '', 'GETPOST', FILTER_SANITIZE_STRING);

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
    } catch (Zikula_Exception_Fatal $e) {
        $return = false;
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
} elseif (isset($e) && $e instanceof Zikula_Exception_Fatal) {
    AjaxUtil::error($e->getMessage());
} elseif ($return === false) {
    // Failed to load the module
    AjaxUtil::error(__f("Could not load the '%s' module (at '%s' function), reason '%s'.", array(DataUtil::formatForDisplay($module), DataUtil::formatForDisplay($func), DataUtil::formatForDisplay($e->getMessage()))));
} else {
    AjaxUtil::output($return, true, false);
}

System::shutdown();

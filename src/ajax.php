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
System::init(System::CORE_STAGES_ALL & ~System::CORE_STAGES_TOOLS & ~System::CORE_STAGES_DECODEURLS);

// Get variables
$module = FormUtil::getPassedValue('module', '', 'GETPOST');
$type   = FormUtil::getPassedValue('type', 'ajax', 'GETPOST');
$func   = FormUtil::getPassedValue('func', '', 'GETPOST');

// Check for site closed
if (System::getVar('siteoff') && !SecurityUtil::checkPermission('Settings::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'Users' && $func == 'siteofflogin')) {
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW) && UserUtil::isLoggedIn()) {
        pnUserLogOut();
    }
    AjaxUtil::error(__('The site is currently off-line.'));
}

if (empty($func)) {
    AjaxUtil::error(__f("Missing parameter '%s'", 'func'));
}

// get module information
$modinfo = ModUtil::getInfo(ModUtil::getIdFromName($module));
if ($modinfo == false) {
    AjaxUtil::error(__f("Error! The '%s' module is unknown.", DataUtil::formatForDisplay($module)));
}

if (!ModUtil::available($modinfo['name'])) {
    AjaxUtil::error(__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($module)));
}

if ($modinfo['type'] == 2 || $modinfo['type'] == 3) {
    // New-new style of loading modules
    if (!isset($arguments)) {
        $arguments = array();
    }

    if (pnModLoad($modinfo['name'], $type)) {
        if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
                $dbConn = pnDBGetConn(true);
                $dbConn->StartTrans();
        }

        // Run the function
        $return = ModUtil::func($modinfo['name'], $type, $func, $arguments);

        if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
            if ($dbConn->HasFailedTrans()) {
                $return = __('Error! The transaction failed. Please perform a rollback.') . "\n" . $return;
                AjaxUtil::error($return);
                $return == true;
            }
            $dbConn->CompleteTrans();
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
} else {
    // Old-old style of loading modules not supported with Ajax
    AjaxUtil::error(__('Error! Ajax support is not implemented for old-style modules.'));
}

System::shutdown();

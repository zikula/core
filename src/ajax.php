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
$core->init(Zikula_Core::STAGE_ALL | Zikula_Core::STAGE_AJAX & ~Zikula_Core::STAGE_DECODEURLS);

// Get variables
$module = FormUtil::getPassedValue('module', '', 'GETPOST', FILTER_SANITIZE_STRING);
$type = FormUtil::getPassedValue('type', 'ajax', 'GETPOST', FILTER_SANITIZE_STRING);
$func = FormUtil::getPassedValue('func', '', 'GETPOST', FILTER_SANITIZE_STRING);

// Check for site closed
if (System::getVar('siteoff') && !SecurityUtil::checkPermission('Settings::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'Users' && $func == 'siteofflogin')) {
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW) && UserUtil::isLoggedIn()) {
        UserUtil::logout();
    }
    die(new Zikula_Response_Ajax_Unavailable(__('The site is currently off-line.')));
}

if (empty($func)) {
    die(new Zikula_Response_Ajax_NotFound(__f("Missing parameter '%s'", 'func')));
}

// get module information
$modinfo = ModUtil::getInfoFromName($module);
if ($modinfo == false) {
    die(new Zikula_Response_Ajax_NotFound(__f("Error! The '%s' module is unknown.", DataUtil::formatForDisplay($module))));
}

if (!ModUtil::available($modinfo['name'])) {
    die(new Zikula_Response_Ajax_NotFound(__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($module))));
}

if (!ModUtil::load($modinfo['name'], $type)) {
    die(new Zikula_Response_Ajax_NotFound(__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($module))));
}

// Handle database transactions
if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
    $dbConn = Doctrine_Manager::getInstance()->getCurrentConnection();
    $dbConn->beginTransaction();
}

// Dispatch controller.
try {
    $response = ModUtil::func($modinfo['name'], $type, $func);
    if (System::isLegacyMode() && $response == false && LogUtil::hasErrors()) {
        throw new Zikula_Exception_Fatal(__('An unknown error occurred in module %s, controller %s, action %s', array($modinfo['name'], $type, $func)));
    }
} catch (Zikula_Exception_NotFound $e) {
    $response = new Zikula_Response_Ajax_NotFound($e->getMessage());
} catch (Zikula_Exception_Forbidden $e) {
    $response = new Zikula_Response_Ajax_Forbidden($e->getMessage());
} catch (Zikula_Exception_Fatal $e) {
    $response = new Zikula_Response_Ajax_Fatal($e->getMessage());
} catch (PDOException $e) {
    $response = new Zikula_Response_Ajax_Fatal($e->getMessage());
} catch (Exception $e) {
    $response = new Zikula_Response_Ajax_Fatal($e->getMessage());
}

// Handle database transactions
if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
    if (isset($e) && $e instanceof Exception) {
        $dbConn->rollback();
    } else {
        $dbConn->commit();
    }
}

// Process final response.
// If response is not instanceof Zikula_Response_Ajax_AbstractBase provide compat solution
if (!$response instanceof Zikula_Response_Ajax_AbstractBase) {
    $response = !is_array($response) ? array('data' => $response) : $response;
    $response['statusmsg'] = LogUtil::getStatusMessages();
    if (System::isLegacyMode()) {
        $response['authid'] = SecurityUtil::generateAuthKey(ModUtil::getName());
    }
    $response = json_encode($response);
    header("HTTP/1.1 200 OK");
    header('Content-type: application/json');
}

// Issue response.
echo $response;
System::shutdown();

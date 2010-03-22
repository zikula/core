<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

include 'lib/ZLoader.php';
ZLoader::register();

// start PN
pnInit(PN_CORE_ALL & ~PN_CORE_AJAX);

if (SessionUtil::hasExpired()) {
    // Session has expired, display warning
    header('HTTP/1.0 403 Access Denied');
    echo pnModAPIFunc('Users', 'user', 'expiredsession');
    Theme::getInstance()->themefooter();
    pnShutDown();
}

// Get variables
$module = FormUtil::getPassedValue('module', null, 'GETPOST');
$type   = FormUtil::getPassedValue('type', 'user', 'GETPOST');
$func   = FormUtil::getPassedValue('func', 'main', 'GETPOST');

// Check for site closed
if (pnConfigGetVar('siteoff') && !SecurityUtil::checkPermission('Settings::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'Users' && $func == 'siteofflogin')) {
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW) && pnUserLoggedIn()) {
        pnUserLogOut();
    }
    header('HTTP/1.1 503 Service Unavailable');
    if (file_exists('config/templates/siteoff.htm')) {
        require_once 'config/templates/siteoff.htm';
    } else {
        require_once 'system/Theme/pntemplates/siteoff.htm';
    }
    pnShutDown();
}

// check requested module and set to start module if not present
if (empty($module)) {
    $module = pnConfigGetVar('startpage');
    $type   = pnConfigGetVar('starttype');
    $func   = pnConfigGetVar('startfunc');
    $args   = explode(',', pnConfigGetVar('startargs'));
    $arguments = array();
    foreach ($args as $arg) {
        if (!empty($arg)) {
            $argument = explode('=', $arg);
            $arguments[$argument[0]] = $argument[1];
            pnQueryStringSetVar($argument[0], $argument[1]);
        }
    }
}

// get module information
$modinfo = pnModGetInfo(pnModGetIDFromName($module));

if ($type <> 'init' && !empty($module) && !pnModAvailable($modinfo['name'])) {
    LogUtil::registerError(__f("The '%s' module is not currently accessible.", DataUtil::formatForDisplay(strip_tags($module))));
    echo pnModFunc('Errors', 'user', 'main', array('type' => 404));
    Theme::getInstance()->themefooter();
    pnShutDown();
}

if ($modinfo['type'] == 2 || $modinfo['type'] == 3) {
    // New-new style of loading modules
    if (!isset($arguments)) {
        $arguments = array();
    }

    // we need to force the mod load if we want to call a modules interactive init
    // function because the modules is not active right now
    $force_modload = ($type=='init') ? true : false;
    if (empty($type)) $type = 'user';
    if (empty($func)) $func = 'main';
    if (pnModLoad($modinfo['name'], $type, $force_modload)) {
        if (pnConfigGetVar('PN_CONFIG_USE_TRANSACTIONS')) {
            $dbConn = pnDBGetConn(true);
            $dbConn->StartTrans();
        }

        $return = pnModFunc($modinfo['name'], $type, $func, $arguments);

        if (pnConfigGetVar('PN_CONFIG_USE_TRANSACTIONS')) {
            if ($dbConn->HasFailedTrans()) {
                $return = __('Error! The transaction failed. Please perform a rollback.') . $return;
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
    if ($return !== true) {
        if ($return === false) {
            // check for existing errors or set a generic error
            if (!LogUtil::hasErrors()) {
                 LogUtil::registerError(__f("Could not load the '%s' module (at '%s' function).", array($modinfo['url'], $func)), 404);
            }
            echo pnModFunc('Errors', 'user', 'main');
        } elseif (is_string($return) && strlen($return) > 1) {
            // Text
            echo $return;
        } elseif (is_array($return)) {
            $pnRender = pnRender::getInstance($modinfo['name']);
            $pnRender->assign($return);
            if (isset($return['template'])) {
                echo $pnRender->fetch($return['template']);
            } else {
                $modname = strtolower($modinfo['name']);
                $type = strtolower($type);
                $func = strtolower($func);
                echo $pnRender->fetch("{$modname}_{$type}_{$func}.htm");
            }
        } else {
            LogUtil::registerError(__f('The \'%1$s\' module returned at the \'%2$s\' function.', array($modinfo['url'], $func)), 404);
            echo pnModFunc('Errors', 'user', 'main');
        }
        Theme::getInstance()->themefooter();
    }
} else {
    Theme::getInstance()->themefooter();
}

pnShutDown();

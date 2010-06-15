<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

include 'lib/ZLoader.php';
ZLoader::register();

System::init(System::CORE_STAGES_ALL & ~System::CORE_STAGES_AJAX);

if (SessionUtil::hasExpired()) {
    // Session has expired, display warning
    header('HTTP/1.0 403 Access Denied');
    echo ModUtil::apiFunc('Users', 'user', 'expiredsession');
    Theme::getInstance()->themefooter();
    System::shutdown();
}

// Get variables
$module = FormUtil::getPassedValue('module', null, 'GETPOST');
$type   = FormUtil::getPassedValue('type', 'user', 'GETPOST');
$func   = FormUtil::getPassedValue('func', 'main', 'GETPOST');

// Check for site closed
if (System::getVar('siteoff') && !SecurityUtil::checkPermission('Settings::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'Users' && $func == 'siteofflogin')) {
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW) && UserUtil::isLoggedIn()) {
        UserUtil::logout();
    }
    header('HTTP/1.1 503 Service Unavailable');
    if (file_exists('config/templates/siteoff.htm')) {
        require_once 'config/templates/siteoff.htm';
    } else {
        require_once 'system/Theme/templates/siteoff.htm';
    }
    System::shutdown();
}

// check requested module and set to start module if not present
if (empty($module)) {
    $module = System::getVar('startpage');
    if (empty($module)) {
        LogUtil::registerError(__f("The requested page coule not be found.", DataUtil::formatForDisplay(strip_tags($module))));
        echo ModUtil::func('Errors', 'user', 'main', array('type' => 404));
        Theme::getInstance()->themefooter();
        System::shutdown();
    }
    $type   = System::getVar('starttype');
    $func   = System::getVar('startfunc');
    $args   = explode(',', System::getVar('startargs'));
    $arguments = array();
    foreach ($args as $arg) {
        if (!empty($arg)) {
            $argument = explode('=', $arg);
            $arguments[$argument[0]] = $argument[1];
            System::queryStringSetVar($argument[0], $argument[1]);
        }
    }
}

// get module information
$modinfo = ModUtil::getInfo(ModUtil::getIdFromName($module));

if ($type <> 'init' && !empty($module) && !ModUtil::available($modinfo['name'])) {
    LogUtil::registerError(__f("The '%s' module is not currently accessible.", DataUtil::formatForDisplay(strip_tags($module))));
    echo ModUtil::func('Errors', 'user', 'main', array('type' => 404));
    Theme::getInstance()->themefooter();
    System::shutdown();
}

// New-new style of loading modules
if (!isset($arguments)) {
    $arguments = array();
}

// we need to force the mod load if we want to call a modules interactive init
// function because the modules is not active right now
$force_modload = ($type=='init') ? true : false;
$type = (empty($type)) ? $type = 'user' : $type;
$func = (empty($func)) ? $func = 'main' : $func;
$return = ModUtil::load($modinfo['name'], $type, $force_modload);
$httpCode = 404;

$message = '';
$debug = null;

if ($return) {
    if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
        $dbConn = System::dbGetConn(true);
        $dbConn->beginTransaction();
    }

    $return = false;

    try {
        $return = ModUtil::func($modinfo['name'], $type, $func, $arguments);
        if (!$return) {
            // hack for BC since modules currently use ModUtil::func without expecting exceptions - drak.
            throw new Zikula_Exception_NotFound(__('Page not found.'));
        }
        $httpCode = 200;

        if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
            $dbConn->commit();
        }
    } catch (Exception $e) {
        $event = new Zikula_Event('frontcontroller.exception', $e, array('modinfo' => $modinfo, 'type' => $type, 'func' => $func, 'arguments' => $arguments));
        EventUtil::notifyUntil($event);
        if ($event->hasNotified()) {
            $httpCode = $event['httpcode'];
            $message = $event['message'];
        } else {
            if ($e instanceof Zikula_Exception_NotFound) {
                $httpCode = 404;
                $message = $e->getMessage();
                $debug = array_merge($e->getDebug(), $e->getTrace());
            } elseif ($e instanceof Zikula_Exception_Forbidden) {
                $httpCode = 403;
                $message = $e->getMessage();
                $debug = array_merge($e->getDebug(), $e->getTrace());
            } elseif ($e instanceof Zikula_Exception_Redirect) {
                System::redirect($e->getUrl(), array(), $e->getType());
                System::shutDown();
            } elseif ($e instanceof PDOException) {
                $httpCode = 500;
                $message = $e->getMessage();
                if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
                    $return = __('Error! The transaction failed. Transaction rolled back.') . $return;
                    $dbConn->rollback();
                } else {
                    $return = __('Error! The transaction failed.') . $return;
                }
            } elseif ($e instanceof Exception) {
                // general catch all
                $httpCode = 500;
                $message = $e->getMessage();
                $debug = $e->getTrace();
            }
        }
    }
}

switch (true)
{
    case ($return === true):
        // prevent rendering of the theme.
        System::shutDown();
        break;
    case ($return === false):
        if (!LogUtil::hasErrors()) {
            LogUtil::registerError(__f('Could not load the \'%1$s\' module at \'%2$s\'. %3$s', array($modinfo['url'], $func, $message)), $httpCode, null, $debug);
        }
        echo ModUtil::func('Errors', 'user', 'main');
        break;
    case ($httpCode == 200):
        echo $return;
        break;
    default:
        LogUtil::registerError(__f('The \'%1$s\' module returned an error in \'%2$s\'. %3$s', array($modinfo['url'], $func, $message)), $httpCode, null, $debug);
        echo ModUtil::func('Errors', 'user', 'main');
        break;
}

Theme::getInstance()->themefooter();
System::shutdown();

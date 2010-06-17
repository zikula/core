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
$module = filter_input(INPUT_GET, 'module', FILTER_SANITIZE_STRING);
$type   = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
$func   = filter_input(INPUT_GET, 'func', FILTER_SANITIZE_STRING);

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
$startPage = System::getVar('startpage');
$arguments = array();
if (!$module) {
    if ((System::getVar('shorturls') && System::getVar('shorturlstype') == 0)) {
        $p = explode('/', str_replace(System::getBaseUri() . '/', '', $_SERVER["REQUEST_URI"]));
        $module = (empty($p[0])) ? $startPage : $p[0];
    } else {
        $module = $startPage;
    }

    $type   = System::getVar('starttype');
    $func   = System::getVar('startfunc');
    $args   = explode(',', System::getVar('startargs'));

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

// we need to force the mod load if we want to call a modules interactive init
// function because the modules is not active right now
$type = (empty($type)) ? $type = 'user' : $type;
$func = (empty($func)) ? $func = 'main' : $func;
if ($type=='init') {
    ModUtil::load($modinfo['name'], $type, true);
}

$httpCode = 404;
$message = '';
$debug = null;
$return = false;

if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
    $dbConn = System::dbGetConn(true);
    $dbConn->beginTransaction();
}

try {
    $return = (empty($module) && empty($startPage)) ? ' ' : ModUtil::func($modinfo['name'], $type, $func, $arguments);
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
                $return = __('Error! The transaction failed. Performing rollback.') . $return;
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

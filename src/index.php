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

if ($modinfo['type'] == ModUtil::TYPE_MODULE || $modinfo['type'] == ModUtil::TYPE_SYSTEM) {
    // New-new style of loading modules
    if (!isset($arguments)) {
        $arguments = array();
    }

    // we need to force the mod load if we want to call a modules interactive init
    // function because the modules is not active right now
    $force_modload = ($type=='init') ? true : false;
    if (empty($type)) $type = 'user';
    if (empty($func)) $func = 'main';
    if (ModUtil::load($modinfo['name'], $type, $force_modload)) {
        if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
            $dbConn = System::dbGetConn(true);
            $dbConn->StartTrans();
        }

        $return = false;
        $httpCode = 404;
        $message = '';
        $debug = null;

        try {
            $return = ModUtil::func($modinfo['name'], $type, $func, $arguments);
        } catch (Zikula_Exception $e) {
            if ($e instanceof Zikula_Exception_NotFound) {
                $httpCode = 404;
                $message = $e->getMessage();
                $debug = $e->getDebug();
            } elseif ($e instanceof Zikula_Exception_Forbidden) {
                $httpCode = 403;
                $message = $e->getMessage();
                $debug = $e->getDebug();
            } elseif ($e instanceof Zikula_Exception_Redirect) {
                System::redirect($e->getUrl);
                System::shutDown();
            } elseif ($e instanceof Exception) {
                // general catch all
                 $httpCode = 500;
                 $message = $e->getMessage();
            }
        }

        if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
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
                 LogUtil::registerError(__f("Could not load the '%s' module (at '%s' function). %s", array($modinfo['url'], $func, $message)), $httpCode, null, $debug);
            }
            echo ModUtil::func('Errors', 'user', 'main');
        } elseif (is_string($return) && strlen($return) > 1) {
            // Text
            echo $return;
        } elseif (is_array($return)) {
            $renderer = Renderer::getInstance($modinfo['name']);
            $renderer->assign($return);
            if (isset($return['template'])) {
                echo $renderer->fetch($return['template']);
            } else {
                $modname = strtolower($modinfo['name']);
                $type = strtolower($type);
                $func = strtolower($func);
                echo $renderer->fetch("{$modname}_{$type}_{$func}.htm");
            }
        } else {
            LogUtil::registerError(__f('The \'%1$s\' module returned at the \'%2$s\' function. %s', array($modinfo['url'], $func, $message)), $httpCode, null, $debug);
            echo ModUtil::func('Errors', 'user', 'main');
        }
    }
}

Theme::getInstance()->themefooter();
System::shutdown();

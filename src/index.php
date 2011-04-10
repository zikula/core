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
$core->init();

if (SessionUtil::hasExpired()) {
    // Session has expired, display warning
    header('HTTP/1.0 403 Access Denied');
    echo ModUtil::apiFunc('Users', 'user', 'expiredsession');
    Zikula_View_Theme::getInstance()->themefooter();
    System::shutdown();
}

// Get variables
$module = FormUtil::getPassedValue('module', '', 'GETPOST', FILTER_SANITIZE_STRING);
$type   = FormUtil::getPassedValue('type', '', 'GETPOST', FILTER_SANITIZE_STRING);
$func   = FormUtil::getPassedValue('func', '', 'GETPOST', FILTER_SANITIZE_STRING);

// Check for site closed
if (System::getVar('siteoff') && !SecurityUtil::checkPermission('Settings::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'Users' && $func == 'siteofflogin') || (Zikula_Core::VERSION_NUM != System::getVar('Version_Num'))) {
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW) && UserUtil::isLoggedIn()) {
        UserUtil::logout();
    }
    header('HTTP/1.1 503 Service Unavailable');
    require_once System::getSystemErrorTemplate('siteoff.tpl');
    System::shutdown();
}

// check requested module and set to start module if not present
$startPage = System::getVar('startpage');
$arguments = array();
if (!$module) {
    if (System::getVar('shorturls')) {
        // remove entry point from the path (otherwise they are part of the module name)
        $customentrypoint = System::getVar('entrypoint');
        $root = empty($customentrypoint) ? 'index.php' : $customentrypoint;
        // REQUEST_URI contains the query string so we use parse_url to get the path without it
        $uri = parse_url($_SERVER["REQUEST_URI"]);
        $p = explode('/', str_replace(array(System::getBaseUri() . '/', "$root"), '', $uri['path']));
        $module = (empty($p[0])) ? $startPage : $p[0];
        if (ZLanguage::isLangParam($module) && in_array($module, ZLanguage::getInstalledLanguages())) {
            $module = '';
        }
    } else {
        $module = $startPage;
    }

    $type = System::getVar('starttype');
    $func = System::getVar('startfunc');
    $args = explode(',', System::getVar('startargs'));

    foreach ($args as $arg) {
        if (!empty($arg)) {
            $argument = explode('=', $arg);
            $arguments[$argument[0]] = $argument[1];
            System::queryStringSetVar($argument[0], $argument[1]);
        }
    }
}

// get module information
$modinfo = ModUtil::getInfoFromName($module);

// we need to force the mod load if we want to call a modules interactive init
// function because the modules is not active right now
if (System::isLegacyMode()) {
    $type = (empty($type)) ? $type = 'user' : $type;
    $func = (empty($func)) ? $func = 'main' : $func;
}
if ($type == 'init' || $type == 'interactiveinstaller') {
    ModUtil::load($modinfo['name'], $type, true);
}

$httpCode = 404;
$message = '';
$debug = null;
$return = false;
$e = null;

if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
    $dbConn = Doctrine_Manager::getInstance()->getCurrentConnection();
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
    $core->getEventManager()->notifyUntil($event);
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
    case ($httpCode == 403):
        if (!UserUtil::isLoggedIn()) {
            $url = ModUtil::url('Users', 'user', 'login', array('returnpage' => urlencode(System::getCurrentUri())));
            LogUtil::registerError(LogUtil::getErrorMsgPermission(), $httpCode, $url);
            System::shutDown();
        }
        // there is no break here deliberately.
    case ($return === false):
        if (!LogUtil::hasErrors()) {
            LogUtil::registerError(__f('Could not load the \'%1$s\' module at \'%2$s\'. %3$s', array($modinfo['url'], $func, $message)), $httpCode, null);
        }
        echo ModUtil::func('Errors', 'user', 'main', array('message' => $message, 'exception' => $e));
        break;
    case ($httpCode == 200):
        echo $return;
        break;
    default:
        LogUtil::registerError(__f('The \'%1$s\' module returned an error in \'%2$s\'. %3$s', array($modinfo['url'], $func, $message)), $httpCode, null);
        echo ModUtil::func('Errors', 'user', 'main', array('message' => $message, 'exception' => $e));
        break;
}

Zikula_View_Theme::getInstance()->themefooter();
System::shutdown();

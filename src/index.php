<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Symfony\Component\HttpFoundation\Request;

include 'lib/bootstrap.php';
$request = Request::createFromGlobals();
$core->getContainer()->set('request', $request);
$core->init();

if ($request->isXmlHttpRequest()) {
    __frontcontroller_ajax();
}
$core->getDispatcher()->dispatch('frontcontroller.predispatch');

$module = FormUtil::getPassedValue('module', '', 'GETPOST', FILTER_SANITIZE_STRING);
$type = FormUtil::getPassedValue('type', '', 'GETPOST', FILTER_SANITIZE_STRING);
$func = FormUtil::getPassedValue('func', '', 'GETPOST', FILTER_SANITIZE_STRING);

// check requested module
$arguments = array();

// process the homepage
if (!$module) {
    // set the start parameters
    $module = System::getVar('startpage');
    $type = System::getVar('starttype');
    $func = System::getVar('startfunc');
    $args = explode(',', System::getVar('startargs'));

    foreach ($args as $arg) {
        if (!empty($arg)) {
            $argument = explode('=', $arg);
            $arguments[$argument[0]] = $argument[1];
        }
    }
}

// get module information
$modinfo = ModUtil::getInfoFromName($module);

// we need to force the mod load if we want to call a modules interactive init
// function because the modules is not active right now
if ($modinfo) {
    $module = $modinfo['url'];

    if ($type == 'init' || $type == 'interactiveinstaller') {
        ModUtil::load($modinfo['name'], $type, true);
    }
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
    if (empty($module)) {
        // we have a static homepage
        $return = ' ';
    } elseif ($modinfo) {
        // call the requested/homepage module
        $return = ModUtil::func($modinfo['name'], $type, $func, $arguments);
    }

    if (!$return) {
        // hack for BC since modules currently use ModUtil::func without expecting exceptions - drak.
        throw new Zikula_Exception_NotFound(__('Page not found.'));
    }
    $httpCode = 200;

    if (System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
        $dbConn->commit();
    }

} catch (Exception $e) {
    $event = new Zikula_Event($e, array('modinfo' => $modinfo, 'type' => $type, 'func' => $func, 'arguments' => $arguments));
    $core->getDispatcher()->dispatch('frontcontroller.exception', $event);

    if ($event->isPropagationStopped()) {
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
                $return = __('Error! The transaction failed. Performing rollback.').$return;
                $dbConn->rollback();
            } else {
                $return = __('Error! The transaction failed.').$return;
            }
        } elseif ($e instanceof Exception) {
            // general catch all
            $httpCode = 500;
            $message = $e->getMessage();
            $debug = $e->getTrace();
        }
    }
}

switch (true) {
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
            LogUtil::registerError(__f('Could not load the \'%1$s\' module at \'%2$s\'.', array($module, $func)), $httpCode, null);
        }
        echo ModUtil::func('Errors', 'user', 'main', array('message' => $message, 'exception' => $e));
        break;

    case ($httpCode == 200):
        echo $return;
        break;

    default:
        LogUtil::registerError(__f('The \'%1$s\' module returned an error in \'%2$s\'.', array($module, $func)), $httpCode, null);
        echo ModUtil::func('Errors', 'user', 'main', array('message' => $message, 'exception' => $e));
        break;
}

Zikula_View_Theme::getInstance()->themefooter();
System::shutdown();

function __frontcontroller_ajax()
{
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
        $response = json_encode($response);
        header("HTTP/1.1 200 OK");
        header('Content-type: application/json');
    }

    // Issue response.
    echo $response;
    System::shutdown();
}

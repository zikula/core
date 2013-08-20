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

use Zikula_Request_Http as Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Core\Response\PlainResponse;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include 'lib/bootstrap.php';

$request = Request::createFromGlobals();
$core->getContainer()->set('request', $request);
$core->init();

//$response = $kernel->handle($request);

//$response->send();
//$kernel->terminate($request, $response);

if ($request->isXmlHttpRequest()) {
    __frontcontroller_ajax($request);
}
$core->getDispatcher()->dispatch('frontcontroller.predispatch', new GenericEvent());

$module = $request->attributes->get('_module');
$type = $request->attributes->get('_type');
$func = $request->attributes->get('_func');

// check requested module
$arguments = $request->attributes->get('_args');

// get module information
$modinfo = ModUtil::getInfoFromName($module);

// we need to force the mod load if we want to call a modules interactive init
// function because the modules is not active right now
if ($modinfo) {
    $module = $modinfo['url'];
}

try {
    if (!$module) {
        // we have a static homepage
        $response = new Response();
    } elseif ($modinfo) {
        // call the requested/homepage module
        $return = ModUtil::func($modinfo['name'], $type, $func, $arguments);
        if (false === $return) {
            // hack for BC since modules currently use ModUtil::func without expecting exceptions - drak.
            $response = new Response(__('Page not found.'), 404);
        } else if (true === $return) {
            // controllers should not return boolean anymore, this is BC for the time being.
            // todo PlainResponse here....
            System::shutDown();
        } else if (false === $return instanceof Response) {
            $response = new Response($return);
        } else {
           $response = $return;
        }
    } else {
        $response = new Response('Something unexpected happened', 500);
    }

} catch (\Exception $e) {
    $event = new GenericEvent($e, array('modinfo' => $modinfo, 'type' => $type, 'func' => $func, 'arguments' => $arguments));
    $core->getDispatcher()->dispatch('frontcontroller.exception', $event);

    if ($event->isPropagationStopped()) {
        $response = new Response($event['message'], $event['httpcode']);
    } else {
        if ($e instanceof Zikula_Exception_NotFound) {
            $response = new Response($e->getMessage(), 404);
            $debug = array_merge($e->getDebug(), $e->getTrace());
        } elseif ($e instanceof NotFoundHttpException) {
            $response = new Response($e->getMessage(), 404);
        } elseif ($e instanceof Zikula_Exception_Forbidden) {
            $response = new Response($e->getMessage(), 403);
            $debug = array_merge($e->getDebug(), $e->getTrace());
        } elseif ($e instanceof Zikula_Exception_Redirect) {
            $response = new RedirectResponse(System::normalizeUrl($e->getUrl()), $e->getType());
        } elseif ($e instanceof PDOException) {
            $response = new Response($e->getMessage(), 500);
        } elseif ($e instanceof \Exception) {
            // general catch all
            $response = new Response($e->getMessage(), 500);
            $debug = $e->getTrace();
        }
    }
}

switch (true) {
    case ($response->getStatusCode() == 200):
        break;

    case ($response->getStatusCode() == 301):
    case ($response->getStatusCode() == 302):
        $response->send();
        System::shutDown();
        break;

    case ($response->getStatusCode() == 403):
        if (!UserUtil::isLoggedIn()) {
            $url = ModUtil::url('ZikulaUsersModule', 'user', 'login', array('returnpage' => urlencode(System::getCurrentUri())));
            $response = new RedirectResponse($url, 302);
            LogUtil::registerError(LogUtil::getErrorMsgPermission(), 403, $url, false);
            $response->send();
            System::shutDown();
        }
        break;

    case ($response->getStatusCode() == 404):
        if (!LogUtil::hasErrors()) {
            LogUtil::registerError(__f('Could not load the \'%1$s\' module at \'%2$s\'.', array($module, $func)), 404, null);
        }
        $errorResponse = ModUtil::func('ZikulaErrorsModule', 'user', 'main', array('message' => isset($e) ? $e->getMessage() : '', 'exception' => isset($e) ? $e: null));
        $response->setContent($errorResponse->getContent());
        break;

    case ($response->getStatusCode() == 500):

    default:
        LogUtil::registerError(__f('The \'%1$s\' module returned an error in \'%2$s\'.', array($module, $func)), 500, null);
        $response = ModUtil::func('ZikulaErrorsModule', 'user', 'main', array('message' => isset($e) ? $e->getMessage() : '', 'exception' => isset($e) ? $e : null));
        break;
}

if (false === $response instanceof PlainResponse) {
    Zikula_View_Theme::getInstance()->themefooter($response);
}

$response->send();
System::shutdown();

function __frontcontroller_ajax(Request $request)
{
    // Get variables
    $module = $request->attributes->get('_module');
    $type = $request->attributes->get('_type', 'ajax');
    $func = $request->attributes->get('_func');

    // get module information
    $modinfo = ModUtil::getInfoFromName($module);

    // Check for site closed
    if (System::getVar('siteoff') && !SecurityUtil::checkPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'ZikulaUsersModule' && $func == 'siteofflogin')) {
        if (SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_OVERVIEW) && UserUtil::isLoggedIn()) {
            UserUtil::logout();
        }
        $response = new \Zikula\Core\Response\Ajax\UnavailableResponse(__('The site is currently off-line.'));
    } else if (empty($func)) {
        $response = new \Zikula\Core\Response\Ajax\NotFoundResponse(__f("Missing parameter '%s'", 'func'));
    } else if ($modinfo == false) {
        $response = new \Zikula\Core\Response\Ajax\NotFoundResponse(__f("Error! The '%s' module is unknown.", DataUtil::formatForDisplay($module)));
    } else if (!ModUtil::available($modinfo['name'])) {
        $response = new \Zikula\Core\Response\Ajax\NotFoundResponse(__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($module)));
    } else if (!ModUtil::load($modinfo['name'], $type)) {
        $response = new \Zikula\Core\Response\Ajax\NotFoundResponse(__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($module)));
    }

    // Dispatch controller.
    try {
        if (!isset($response)) {
            $response = ModUtil::func($modinfo['name'], $type, $func);
            if (System::isLegacyMode() && $response == false && LogUtil::hasErrors()) {
                throw new Zikula_Exception_Fatal(__('An unknown error occurred in module %s, controller %s, action %s', array($modinfo['name'], $type, $func)));
            }
        }
    } catch (Zikula_Exception_NotFound $e) {
        $response = new \Zikula\Core\Response\Ajax\NotFoundResponse($e->getMessage());
    } catch (Zikula_Exception_Forbidden $e) {
        $response = new \Zikula\Core\Response\Ajax\ForbiddenResponse($e->getMessage());
    } catch (\Exception $e) {
        $response = new \Zikula\Core\Response\Ajax\FatalResponse($e->getMessage());
    }

    // Process final response.
    // If response is not instanceof Response provide compat solution
    if (!$response instanceof Response) {
        $response = !is_array($response) ? array('data' => $response) : $response;
        $response['statusmsg'] = LogUtil::getStatusMessages();
        $response = json_encode($response);
        header("HTTP/1.1 200 OK");
        header('Content-type: application/json');
        echo $response;
    } else {
        // Issue response.
        $response->send();
    }

    System::shutdown();
}

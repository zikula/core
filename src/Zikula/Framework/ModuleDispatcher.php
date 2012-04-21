<?php

/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Framework;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Framework\Response\PlainResponse;
use Zikula\Framework\Response\Ajax\NotFoundResponse;
use Zikula\Framework\Response\Ajax\UnavailableResponse;
use Zikula\Framework\Response\Ajax\ForbiddenResponse;
use Zikula\Framework\Response\Ajax\FatalResponse;
use Zikula\Framework\Exception\NotFoundException;
use Zikula\Framework\Exception\FatalException;
use Zikula\Framework\Exception\ForbiddenException;

class ModuleDispatcher
{
    public function dispatch(Request $request)
    {
        $module = $request->attributes->get('_module');
        $type = $request->attributes->get('_controller');
        $func = $request->attributes->get('_action');

        $arguments = array();

        // process the homepage
        if (!$module) {
            // set the start parameters
            $module = \System::getVar('startpage');
            $type = \System::getVar('starttype');
            $func = \System::getVar('startfunc');
            $args = explode(',', \System::getVar('startargs'));

            foreach ($args as $arg) {
                if (!empty($arg)) {
                    $argument = explode('=', $arg);
                    $arguments[$argument[0]] = $argument[1];
                }
            }
        }

        // get module information
        $modinfo = \ModUtil::getInfoFromName($module);

        // we need to force the mod load if we want to call a modules interactive init
        // function because the modules is not active right now
        if ($modinfo) {
            $module = $modinfo['url'];

            if ($type == 'init' || $type == 'interactiveinstaller') {
                \ModUtil::load($modinfo['name'], $type, true);
            }
        }

        $httpCode = 404;
        $message = '';
        $debug = null;
        $return = false;
        $e = null;

        try {
            if (empty($module)) {
                // we have a static homepage
                return new Response();
            } elseif ($modinfo) {
                // call the requested/homepage module
                $return = \ModUtil::func($modinfo['name'], $type, $func, $arguments);
            }

            if (!$return) {
                // hack for BC since modules currently use ModUtil::func without expecting exceptions
                // if a controller is not found the API will return false.
                throw new \Zikula\Framework\Exception\NotFoundException(__('Page not found.'));
            }

            return $return;
        } catch (\Exception $e) {
            if ($e instanceof \Zikula\Framework\Exception\NotFoundException) {
                $httpCode = 404;
                $message = $e->getMessage();
                $debug = array_merge($e->getDebug(), $e->getTrace());
            } elseif ($e instanceof \Zikula\Framework\Exception\ForbiddenException) {
                $httpCode = 403;
                $message = $e->getMessage();
                $debug = array_merge($e->getDebug(), $e->getTrace());
            } elseif ($e instanceof \Zikula\Framework\Exception\RedirectException) {
                return new RedirectResponse($e->getUrl(), array(), $e->getType());
            } elseif ($e instanceof \Exception) {
                // general catch all
                $httpCode = 500;
                $message = $e->getMessage();
                $debug = $e->getTrace();
            }
        }

        switch (true) {
            case ($httpCode == 403):
                if (!\UserUtil::isLoggedIn()) {
                    $url = \ModUtil::url('Users', 'user', 'login', array('returnpage' => urlencode(\System::getCurrentUri())));
                    return \LogUtil::registerError(\LogUtil::getErrorMsgPermission(), $httpCode, $url);
                }
            // there is no break here deliberately.
            case ($return === false):
                $session = \ServiceUtil::get('session');
                if (!$session->getFlashBag()->has(\Zikula_Session::MESSAGE_ERROR)) {
                    \LogUtil::registerError(__f('Could not load the \'%1$s\' module at \'%2$s\'.', array($module, $func)), $httpCode, null);
                }
                return \ModUtil::func('ErrorsModule', 'user', 'index', array('message' => $message, 'exception' => $e));
                break;

             default:
                \LogUtil::registerError(__f('The \'%1$s\' module returned an error in \'%2$s\'.', array($module, $func)), $httpCode, null);
                return \ModUtil::func('ErrorsModule', 'user', 'index', array('message' => $message, 'exception' => $e));
                break;
        }
    }

    public function ajaxDispatch(Request $request)
    {
        $module = $request->attributes->get('_module');
        $type = $request->attributes->get('_controller');
        $func = $request->attributes->get('_action');

        if (empty($func)) {
            $response = new NotFoundResponse(__f("Missing parameter '%s'", 'func'));
        }

        // get module information
        $modinfo = \ModUtil::getInfoFromName($module);
        if ($modinfo == false) {
            $response = new NotFoundResponse(__f("Error! The '%s' module is unknown.", \DataUtil::formatForDisplay($module)));
        }

        if (!\ModUtil::available($modinfo['name'])) {
            $response = new NotFoundResponse(__f("Error! The '%s' module is not available.", \DataUtil::formatForDisplay($module)));
        }

        if (!\ModUtil::load($modinfo['name'], $type)) {
            $response = new NotFoundResponse(__f("Error! The '%s' module is not available.", \DataUtil::formatForDisplay($module)));
        }

        // Dispatch controller.
        try {
            $response = \ModUtil::func($modinfo['name'], $type, $func);
        } catch (NotFoundException $e) {
            $response = new NotFoundResponse($e->getMessage());
        } catch (ForbiddenException $e) {
            $response = new ForbiddenResponse($e->getMessage());
        } catch (FatalException $e) {
            $response = new FatalResponse($e->getMessage());
        } catch (\PDOException $e) {
            $response = new FatalResponse($e->getMessage());
        } catch (\Exception $e) {
            $response = new FatalResponse($e->getMessage());
        }

        // Issue response.
        return $response;
    }
}

<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Bundle\CoreBundle\EventListener;

use Psr\Log\LoggerInterface;
use Zikula\Core\Exception\FatalErrorException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\FatalResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;
use Zikula\Core\Response\Ajax\NotFoundResponse;
use Zikula\Core\Response\Ajax\UnavailableResponse;
use ModUtil;
use UserUtil;
use LogUtil;
use System;
use Zikula_View_Theme;
use SecurityUtil;
use PageUtil;
use Zikula\Core\Response\PlainResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController;

class LegacyRouteListener implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== Kernel::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        if ($request->isXmlHttpRequest()) {
            return $this->ajax($event);
        }

        $module = $request->attributes->get('_zkModule');
        $type = $request->attributes->get('_zkType');
        $func = $request->attributes->get('_zkFunc');
        $arguments = $request->attributes->get('_zkArgs');

        // get module information
        $modinfo = ModUtil::getInfoFromName($module);
        if (!$module) {
            // module could not be filtered from url.
            $path = $event->getRequest()->getPathInfo();
            if ($path == "" || $path == "/") {
                // we have a static homepage
                $response = new Response('');
            } else {
                $response = new Response(__('Page not found.'), 404);
            }

            return $this->setResponse($event, $response);
        } else {
            try {
                if (!$modinfo) {
                    $response = new Response(__('Page not found.'), 404);

                    return $this->setResponse($event, $response);
                }

                // call the requested/homepage module
                try {
                    ModUtil::getModule($module);
                    $newType = true;
                } catch (\Exception $e) {
                    $newType = false;
                }

                if ($newType) {
                    $return = ModUtil::func($modinfo['name'], $type, $func);
                } else {
                    $return = ModUtil::func($modinfo['name'], $type, $func, $arguments);
                }

                if (false === $return) {
                    // hack for BC since modules currently use ModUtil::func without expecting exceptions - drak.
                    $response = new Response(__('Page not found.'), 404);

                    return $this->setResponse($event, $response);
                } else {
                    if (true === $return) {
                        // controllers should not return boolean anymore, this is BC for the time being.
                        $response = new PlainResponse();
                    } else {
                        if (false === $return instanceof Response) {
                            $response = new Response($return);
                        } else {
                            $response = $return;
                        }
                    }
                }
            } catch (\Exception $e) {
                if ($e instanceof NotFoundHttpException) {
                    $response = new Response($e->getMessage(), 404);
                } elseif ($e instanceof AccessDeniedException) {
                    $response = new Response($e->getMessage(), 403);
                } elseif ($e instanceof \Zikula_Exception_Redirect) {
                    $response = new RedirectResponse(System::normalizeUrl($e->getUrl()), $e->getType());
                } else {
                    throw $e;
                }
            }
            if ($response->getStatusCode() == 403 && !UserUtil::isLoggedIn()) {
                $url = ModUtil::url(
                    'ZikulaUsersModule',
                    'user',
                    'login',
                    array('returnpage' => urlencode($request->getSchemeAndHttpHost().$request->getRequestUri()))
                );
                $response = new RedirectResponse($url, 302);
                $errorMessage = $e->getMessage();
                LogUtil::registerError(!empty($errorMessage) ? $errorMessage : LogUtil::getErrorMsgPermission(), 403, $url);
                $this->setResponse($event, $response);
            }
        }
        $this->setResponse($event, $response);
    }

    private function ajax(GetResponseEvent $event)
    {
        // Get variables
        $request = $event->getRequest();
        $response = null;
        $module = $request->attributes->get('_zkModule');
        $type = $request->attributes->get('_zkType', 'ajax');
        $func = $request->attributes->get('_zkFunc');

        // get module information
        $modinfo = ModUtil::getInfoFromName($module);

        // Check for site closed
        if (System::getVar('siteoff') && !SecurityUtil::checkPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'ZikulaUsersModule' && $func == 'siteofflogin')) {
            if (SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_OVERVIEW) && UserUtil::isLoggedIn()) {
                UserUtil::logout();
            }
            $response = new UnavailableResponse(__('The site is currently off-line.'));
        }

        // Dispatch controller.
        try {
            if (!isset($response)) {
                $response = ModUtil::func($modinfo['name'], $type, $func);
                if (System::isLegacyMode() && $response == false && LogUtil::hasErrors()) {
                    throw new FatalErrorException(__(
                        'An unknown error occurred in module %s, controller %s, action %s',
                        array($modinfo['name'], $type, $func)
                    ));
                }
                // BC
                if ($response === true) {
                    // Do not return an ajax response but a normal, empty response for BC here.
                    $response = new Response();
                }
            }
        } catch (NotFoundHttpException $e) {
            $response = new NotFoundResponse($e->getMessage());
        } catch (AccessDeniedException $e) {
            $response = new ForbiddenResponse($e->getMessage());
        } catch (\Exception $e) {
            $response = new FatalResponse($e->getMessage());
        }

        // Process final response.
        // If response is not instanceof Response provide compat solution
        if (!$response instanceof Response) {
            $response = new AjaxResponse($response, LogUtil::getStatusMessages());
        }

        return $this->setResponse($event, $response);
    }

    public function onException(GetResponseForExceptionEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $exception = $event->getException();
        if ($exception instanceof AccessDeniedException && !UserUtil::isLoggedIn()) {
            $url = ModUtil::url(
                'ZikulaUsersModule',
                'user',
                'login',
                array('returnpage' => urlencode($request->getSchemeAndHttpHost().$request->getRequestUri()))
            );
            $response = new RedirectResponse($url, 302);
            LogUtil::registerError(LogUtil::getErrorMsgPermission(), 403, $url, false);
            $this->setResponse($event, $response);
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('onKernelRequest', 31),
            )
        );
    }

    private function setResponse(GetResponseEvent $event, Response $response)
    {
        $response->legacy = true;
        $event->setResponse($response);
    }
}

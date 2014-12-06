<?php

/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use UserUtil;

/**
 * ExceptionListener catches exceptions and converts them to Response instances.
 */
class ExceptionListener implements EventSubscriberInterface
{
    private $logger;
    private $router;
    private $dispatcher;

    public function __construct(LoggerInterface $logger = null, RouterInterface $router = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->logger = $logger;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array(
                array('onKernelException', 31),
            )
        );
    }

    /**
     * Handles exceptions.
     *
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // for BC only, remove in 2.0.0
        $this->handleLegacyExceptionEvent($event);

        if (!$event->getRequest()->isXmlHttpRequest()) {
            $exception = $event->getException();
            $userLoggedIn = UserUtil::isLoggedIn();
            do {
                if ($exception instanceof AccessDeniedException) {
                    $this->handleAccessDeniedException($event, $userLoggedIn);
                } elseif ($exception instanceof RouteNotFoundException) {
                    if ($userLoggedIn) {
                        $this->handleRouteNotFoundException($event);
                    }
                }
                // list and handle additional exceptions here
            } while (null !== $exception = $exception->getPrevious());

            // force all exception to render in BC theme (remove in 2.0.0)
            $event->getRequest()->attributes->set('_legacy', true);
        }
    }

    /**
     * Handle an AccessDeniedException
     *
     * @param GetResponseForExceptionEvent $event
     * @param $userLoggedIn
     */
    private function handleAccessDeniedException(GetResponseForExceptionEvent $event, $userLoggedIn)
    {
        if (!$userLoggedIn) {
            $event->getRequest()->getSession()->getFlashBag()->add('error', __('You do not have permission. You must login first.'));
            $params = array('returnpage' => urlencode($event->getRequest()->getSchemeAndHttpHost() . $event->getRequest()->getRequestUri()));
            // redirect to login page
            $route = $this->router->generate('zikulausersmodule_user_login', $params, RouterInterface::ABSOLUTE_URL);
        } else {
            $event->getRequest()->getSession()->getFlashBag()->add('error', __('You do not have permission for that action.'));
            // redirect to previous page
            $route = $event->getRequest()->server->get('HTTP_REFERER', \System::getHomepageUrl());
        }
        // optionally add logging action here

        $response = new RedirectResponse($route);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * Handle an RouteNotFoundException
     *
     * @param GetResponseForExceptionEvent $event
     */
    private function handleRouteNotFoundException(GetResponseForExceptionEvent $event)
    {
        if (\SecurityUtil::checkPermission('ZikulaRoutesModule::', '::', ACCESS_ADMIN)) {
            $message = $event->getException()->getMessage();
            $event->getRequest()->getSession()->getFlashBag()->add('error', "$message<br />" . __('You might try re-loading the routes for the extension in question.'));
            $event->setResponse(new RedirectResponse($this->router->generate('zikularoutesmodule_route_reload', array('lct' => 'admin'), RouterInterface::ABSOLUTE_URL)));
            $event->stopPropagation();
        }
    }

    /**
     * Dispatch and handle the legacy event `frontcontroller.exception`
     *
     * @deprecated removal scheduled for 2.0.0
     *
     * @param GetResponseForExceptionEvent $event
     */
    private function handleLegacyExceptionEvent(GetResponseForExceptionEvent $event)
    {
        $modinfo = \ModUtil::getInfoFromName($event->getRequest()->attributes->get('_zkModule'));
        $legacyEvent = new \Zikula\Core\Event\GenericEvent($event->getException(),
            array('modinfo' => $modinfo,
                'type' => $event->getRequest()->attributes->get('_zkType'),
                'func' => $event->getRequest()->attributes->get('_zkFunc'),
                'arguments' => $event->getRequest()->attributes->all()));
        $this->dispatcher->dispatch('frontcontroller.exception', $legacyEvent);
        if ($legacyEvent->isPropagationStopped()) {
            $event->getRequest()->getSession()->getFlashBag()->add('error', __f('The \'%1$s\' module returned an error in \'%2$s\'. (%3$s)', array(
                $event->getRequest()->attributes->get('_zkModule'),
                $event->getRequest()->attributes->get('_zkFunc'),
                $legacyEvent->getArgument('message'))),
                    $legacyEvent->getArgument('httpcode'));
            $route = $event->getRequest()->server->get('referrer');
            $response = new RedirectResponse($route);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}

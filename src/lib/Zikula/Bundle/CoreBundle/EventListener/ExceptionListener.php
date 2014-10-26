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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
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

    public function __construct(LoggerInterface $logger = null, RouterInterface $router = null)
    {
        $this->logger = $logger;
        $this->router = $router;
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
        $exception = $event->getException();
        $userLoggedIn = UserUtil::isLoggedIn();
        do {
            if ($exception instanceof AccessDeniedException) {
                $this->handleAccessDeniedException($event, $userLoggedIn);
            }
            // list and handle additional exceptions here
        } while (null !== $exception = $exception->getPrevious());
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
            $route = $this->router->generate('zikulausersmodule_user_login', $params);
        } else {
            $event->getRequest()->getSession()->getFlashBag()->add('error', __('You do not have permission for that action.'));
            // redirect to previous page
            $route = $event->getRequest()->server->get('referrer');
        }
        // optionally add logging action here

        $response = new RedirectResponse($route);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * ExceptionListener catches exceptions and converts them to Response instances.
 */
class ExceptionListener implements EventSubscriberInterface
{
    private $logger;
    private $router;
    private $dispatcher;
    private $cacheClearer;
    private $currentUserApi;
    private $installed;

    public function __construct(
        LoggerInterface $logger = null,
        RouterInterface $router = null,
        EventDispatcherInterface $dispatcher = null,
        CacheClearer $cacheClearer,
        CurrentUserApi $currentUserApi,
        $installed
    ) {
        $this->logger = $logger;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
        $this->cacheClearer = $cacheClearer;
        $this->currentUserApi = $currentUserApi;
        $this->installed = $installed;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 31]
            ]
        ];
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
            do {
                $userLoggedIn = $this->installed ? $this->currentUserApi->isLoggedIn() : false;
                if ($exception instanceof AccessDeniedException) {
                    $this->handleAccessDeniedException($event, $userLoggedIn, $exception->getMessage());
                } elseif ($exception instanceof RouteNotFoundException) {
                    $this->handleRouteNotFoundException($event, $userLoggedIn);
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
     * @param string $message a custom error message (default: 'Access Denied') (The default message from Symfony)
     * @see http://api.symfony.com/2.6/Symfony/Component/Security/Core/Exception/AccessDeniedException.html
     */
    private function handleAccessDeniedException(GetResponseForExceptionEvent $event, $userLoggedIn, $message = 'Access Denied')
    {
        if (!$userLoggedIn) {
            $message = ($message == 'Access Denied') ? __('You do not have permission. You must login first.') : $message;
            $event->getRequest()->getSession()->getFlashBag()->add('error', $message);

            $params = ['returnUrl' => urlencode($event->getRequest()->getRequestUri())];
            // redirect to login page
            $route = $this->router->generate('zikulausersmodule_access_login', $params, RouterInterface::ABSOLUTE_URL);
        } else {
            $message = ($message == 'Access Denied') ? __('You do not have permission for that action.') : $message;
            $event->getRequest()->getSession()->getFlashBag()->add('error', $message);

            // redirect to previous page
            $route = $event->getRequest()->server->get('HTTP_REFERER', $this->router->generate('home'));
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
     * @param $userLoggedIn
     */
    private function handleRouteNotFoundException(GetResponseForExceptionEvent $event, $userLoggedIn)
    {
        $message = $event->getException()->getMessage();
        $event->getRequest()->getSession()->getFlashBag()->add('error', $message);
        if ($userLoggedIn && \SecurityUtil::checkPermission('ZikulaRoutesModule::', '::', ACCESS_ADMIN)) {
            try {
                $url = $this->router->generate('zikularoutesmodule_route_reload', ['lct' => 'admin'], RouterInterface::ABSOLUTE_URL);
                $link = "<a href='$url'>". __('re-loading the routes') . "</a>";
                $event->getRequest()->getSession()->getFlashBag()->add('error', __f('You might try %s for the extension in question.', $link));
            } catch (RouteNotFoundException $e) {
            }
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
            [
                'modinfo' => $modinfo,
                'type' => $event->getRequest()->attributes->get('_zkType'),
                'func' => $event->getRequest()->attributes->get('_zkFunc'),
                'arguments' => $event->getRequest()->attributes->all()
            ]
        );
        $this->dispatcher->dispatch('frontcontroller.exception', $legacyEvent);
        if ($legacyEvent->isPropagationStopped()) {
            $event->getRequest()->getSession()->getFlashBag()->add('error', __f('The \'%1$s\' module returned an error in \'%2$s\'. (%3$s)', [
                $event->getRequest()->attributes->get('_zkModule'),
                $event->getRequest()->attributes->get('_zkFunc'),
                $legacyEvent->getArgument('message')
            ]), $legacyEvent->getArgument('httpcode'));
            $route = $event->getRequest()->server->get('referrer');
            $response = new RedirectResponse($route);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

/**
 * ExceptionListener catches exceptions and converts them to Response instances.
 */
class ExceptionListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        CurrentUserApiInterface $currentUserApi
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->currentUserApi = $currentUserApi;
        $this->installed = '0.0.0' !== $_ENV['ZIKULA_INSTALLED'];
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
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $exception = $event->getThrowable();
        do {
            $userLoggedIn = $this->installed ? $this->currentUserApi->isLoggedIn() : false;
            if ($exception instanceof AccessDeniedException) {
                $this->handleAccessDeniedException($event, $userLoggedIn, $exception->getMessage());
            } elseif (!$userLoggedIn && $exception instanceof NotFoundHttpException) {
                // if a record is not found for a guest this could likely be a result of missing permissions
                // thus, treat it like access denied and redirect to login page
                $this->handleAccessDeniedException($event, $userLoggedIn, $exception->getMessage());
            }
            // list and handle additional exceptions here
        } while (null !== $exception = $exception->getPrevious());
    }

    /**
     * Handle an AccessDeniedException
     *
     * @see AccessDeniedException
     */
    private function handleAccessDeniedException(ExceptionEvent $event, bool $userLoggedIn, string $message = 'Access Denied'): void
    {
        $session = null !== $event->getRequest() && $event->getRequest()->hasSession()
            && null !== $event->getRequest()->getSession() ? $event->getRequest()->getSession() : null;
        if (!$userLoggedIn) {
            if (null !== $session) {
                $message = 'Access Denied.' === $message
                    ? $this->translator->trans('You do not have permission. You must login first.')
                    : $message
                ;
                $session->getFlashBag()->add('error', $message);
            }

            $params = ['returnUrl' => urlencode($event->getRequest()->getRequestUri())];
            // redirect to login page
            $route = $this->router->generate('zikulausersmodule_access_login', $params, RouterInterface::ABSOLUTE_URL);
        } else {
            if (null !== $session) {
                $message = 'Access Denied.' === $message
                    ? $this->translator->trans('You do not have permission for that action.')
                    : $message
                ;
                $session->getFlashBag()->add('error', $message);
            }

            // redirect to previous page
            $route = $event->getRequest()->server->get('HTTP_REFERER', $this->router->generate('home'));
        }
        // optionally add logging action here

        $response = new RedirectResponse($route);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}

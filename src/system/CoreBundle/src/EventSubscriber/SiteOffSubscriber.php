<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CoreBundle\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Zikula\CoreBundle\Response\PlainResponse;

class SiteOffSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly RouterInterface $router,
        private readonly Environment $twig,
        private readonly bool $maintenanceModeEnabled,
        private readonly ?string $maintenanceReason
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // priority set high to catch request before other subscribers
            KernelEvents::REQUEST => ['onKernelRequestSiteOff', 110],
        ];
    }

    public function onKernelRequestSiteOff(RequestEvent $event): void
    {
        if (!$this->maintenanceModeEnabled) {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        $this->router->getContext()->setBaseUrl($request->getBaseUrl());
        try {
            $routeInfo = $this->router->match($request->getPathInfo());
        } catch (\Exception) {
            return;
        }
        if ('nucleos_user_security_login' === $routeInfo['_route']) {
            return;
        }
        if ($response instanceof PlainResponse
            || $response instanceof JsonResponse
            || $request->isXmlHttpRequest()) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $hasOnlyBasicAccess = !$this->security->isGranted('IS_AUTHENTICATED');
        if ($hasOnlyBasicAccess && $request->hasSession() && null !== $request->getSession()) {
            $request->getSession()->invalidate(); // logout
        }
        $response = new PlainResponse();
        $response->headers->add(['HTTP/1.1 503 Service Unavailable']);
        $response->setStatusCode(503);
        $content = $this->twig->render('@Core/System/siteoff.html.twig', [
            'reason' => $this->maintenanceReason,
        ]);
        $response->setContent($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}

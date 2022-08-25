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

namespace Zikula\Bundle\CoreBundle\EventListener;

use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;

class SiteOffListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly PermissionApiInterface $permissionApi,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly Environment $twig,
        private readonly RouterInterface $router,
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

        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        $this->router->getContext()->setBaseUrl($request->getBaseUrl());
        try {
            $routeInfo = $this->router->match($request->getPathInfo());
        } catch (Exception) {
            return;
        }
        if (
            'zikulausersbundle_access_login' === $routeInfo['_route']
            || 'zikulathemebundle_combinedasset_asset' === $routeInfo['_route']
        ) {
            return;
        }
        if ($response instanceof PlainResponse
            || $response instanceof JsonResponse
            || $request->isXmlHttpRequest()) {
            return;
        }

        $hasAdminPermissions = $this->permissionApi->hasPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN);
        if ($hasAdminPermissions) {
            return;
        }

        $hasOnlyOverviewAccess = $this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_OVERVIEW);
        if ($hasOnlyOverviewAccess && $request->hasSession() && null !== $request->getSession() && $this->currentUserApi->isLoggedIn()) {
            $request->getSession()->invalidate(); // logout
        }
        $response = new PlainResponse();
        $response->headers->add(['HTTP/1.1 503 Service Unavailable']);
        $response->setStatusCode(503);
        $content = $this->twig->render('CoreBundle:System:siteoff.html.twig', [
            'reason' => $this->maintenanceReason,
        ]);
        $response->setContent($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}

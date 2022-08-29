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

namespace Zikula\ThemeBundle\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeBundle\Controller\Dashboard\AdminDashboardController;
use Zikula\ThemeBundle\Controller\Dashboard\UserDashboardController;
use Zikula\ThemeBundle\Engine\Engine;

/**
 * This class intercepts the Response and modifies it to return a themed Response.
 */
class CreateThemedResponseListener implements EventSubscriberInterface
{
    private bool $installed;

    private bool $debug;

    public function __construct(
        private readonly Engine $themeEngine,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        string $installed,
        string $debug,
    ) {
        $this->installed = '0.0.0' !== $installed;
        $this->debug = !empty($debug);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['redirectToEasyAdmin'],
            KernelEvents::RESPONSE => ['createThemedResponse', -2],
        ];
    }

    public function redirectToEasyAdmin(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMasterRequest() || $request->isXmlHttpRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }

        $route = $request->attributes->get('_route', '');
        if (str_starts_with($route, 'home')) { // TODO remove hardcoded assumption
            return;
        }
        $routeParameters = $request->attributes->get('_route_params');

        // TODO utilize theme name
        // $dashboard = UserDashboardController::class;
        $dashboard = AdminDashboardController::class;

        // menu indexes
        $index = -1;
        $subIndex = -1;

        $url = $this->adminUrlGenerator
            ->setDashboard($dashboard)
            ->setRoute($route, $routeParameters)
            ->set(EA::MENU_INDEX, $index)
            ->set(EA::SUBMENU_INDEX, $subIndex)
            ->generateUrl()
        ;
        // $event->setResponse(new RedirectResponse($url));
        return;

        $queryParams = $requestParams = $attributes = [];
        $attributes['_controller'] = [$dashboard, 'index'];
        $attributes['_route'] = $route;
        $attributes['_route_params'] = $routeParameters;

        $subRequest = $request->duplicate($queryParams, $requestParams, $attributes);

        $event->setResponse(
            $event->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST)
        );
        /**
        TODO

        check if EAB is available
        check if EAB is activated for admin/user area (ThemeBundle configuration)

        Benefit: works with plain Symfony as well as EAB!
         */
    }

    public function createThemedResponse(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }
        return;

        $request = $event->getRequest();
        $response = $event->getResponse();
        $format = $request->getRequestFormat();
        $route = $request->attributes->get('_route', '');
        if (!($response instanceof Response)
            || 'html' !== $format
            || str_starts_with($route, '_') // the profiler and other symfony routes begin with '_' @todo this is still too permissive
            || is_subclass_of($response, Response::class)
            || $request->isXmlHttpRequest()
            || !str_contains($response->headers->get('Content-Type'), 'text/html')
            || $this->debug && ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)
        ) {
            return;
        }

        // all responses are assumed to be themed. PlainResponse will have already returned.
        $twigThemedResponse = $this->themeEngine->wrapResponseInTheme($response);
        $event->setResponse($twigThemedResponse);
    }
}

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

namespace Zikula\ThemeBundle\EventSubscriber;

use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeBundle\Controller\Dashboard\UserDashboardController;

class CreateThemedResponseSubscriber implements EventSubscriberInterface
{
    private bool $installed;

    private bool $debug;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        #[Autowire('%env(ZIKULA_INSTALLED)%')]
        string $installed,
        #[Autowire('%env(APP_DEBUG)%')]
        string $debug,
    ) {
        $this->installed = '0.0.0' !== $installed;
        $this->debug = !empty($debug);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['createThemedResponse'],
        ];
    }

    public function createThemedResponse(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || $request->isXmlHttpRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }

        $route = $request->attributes->get('_route', '');
        if (str_starts_with($route, 'home')) { // TODO remove hardcoded assumption -> check if controller is a dashboard
            return;
        }

        $dashboard = UserDashboardController::class; // TODO $this->themeEngine->getActiveDashboardControllerClass();
        $routeParameters = $request->attributes->get('_route_params');

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
}

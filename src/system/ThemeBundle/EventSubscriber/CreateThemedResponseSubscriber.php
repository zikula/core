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
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\DashboardControllerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Factory\AdminContextFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\ControllerFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeBundle\Helper\FallbackDashboardDetector;

class CreateThemedResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AdminContextFactory $adminContextFactory,
        private readonly ControllerFactory $controllerFactory,
        private readonly FallbackDashboardDetector $dashboardDetector
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -50], // run after AdminRouterSubscriber (has default priority 0)
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (null !== $adminContext = $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE)) {
            // nothing to do if we already have an EA context
            return;
        }

        if (null === $dashboardControllerFqcn = $this->getDashboardControllerFqcn($request)) {
            // current controller is not a dashboard controller, so add a fallback
            $dashboardControllerFqcn = $this->dashboardDetector->getDashboardControllerFqcn($request);
        }

        if (null === $dashboardControllerInstance = $this->getDashboardControllerInstance($dashboardControllerFqcn, $request)) {
            return;
        }

        // $crudControllerInstance is always null atm but this seems fine as we are dealing with a regular Symfony controller
        $crudControllerInstance = $this->getCrudControllerInstance($request);
        $adminContext = $this->adminContextFactory->create($request, $dashboardControllerInstance, $crudControllerInstance);
        $request->attributes->set(EA::CONTEXT_REQUEST_ATTRIBUTE, $adminContext);

        $request->query->set(EA::ROUTE_NAME, $request->attributes->get('_route'));
        $request->query->set(EA::ROUTE_PARAMS, $request->attributes->get('_route_params'));
    }

    /**
     * Copy of AdminRouterSubscriber#getDashboardControllerFqcn.
     */
    private function getDashboardControllerFqcn(Request $request): ?string
    {
        $controller = $request->attributes->get('_controller');
        $controllerFqcn = null;

        if (\is_string($controller)) {
            [$controllerFqcn, ] = explode('::', $controller);
        }

        if (\is_array($controller)) {
            $controllerFqcn = $controller[0];
        }

        if (\is_object($controller)) {
            $controllerFqcn = $controller::class;
        }

        return is_subclass_of($controllerFqcn, DashboardControllerInterface::class) ? $controllerFqcn : null;
    }

    /**
     * Copy of AdminRouterSubscriber#getDashboardControllerInstance.
     */
    private function getDashboardControllerInstance(string $dashboardControllerFqcn, Request $request): ?DashboardControllerInterface
    {
        return $this->controllerFactory->getDashboardControllerInstance($dashboardControllerFqcn, $request);
    }

    /**
     * Copy of AdminRouterSubscriber#getCrudControllerInstance.
     */
    private function getCrudControllerInstance(Request $request): ?CrudControllerInterface
    {
        $crudControllerFqcn = $request->query->get(EA::CRUD_CONTROLLER_FQCN);

        $crudAction = $request->query->get(EA::CRUD_ACTION);

        return $this->controllerFactory->getCrudControllerInstance($crudControllerFqcn, $crudAction, $request);
    }
}

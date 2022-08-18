<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\RoutesBundle\Event\RoutesNewlyAvailableEvent;
use Zikula\RoutesBundle\Helper\RouteDumperHelper;

/**
 * Event handler implementation class for module installer events.
 */
class InstallerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            RoutesNewlyAvailableEvent::class => ['newRoutesAvail', 5]
        ];
    }

    public function __construct(
        private readonly RouteDumperHelper $routeDumperHelper,
        private readonly RequestStack $requestStack
    ) {
    }

    public function newRoutesAvail(RoutesNewlyAvailableEvent $event): void
    {
        // reload **all** JS routes
        $this->updateJsRoutes();
    }

    private function updateJsRoutes(): void
    {
        $errors = $this->routeDumperHelper->dumpJsRoutes();
        if ('' === $errors) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->getFlashBag()->add('error', $errors);
        }
    }
}

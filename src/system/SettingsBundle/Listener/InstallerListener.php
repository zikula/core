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

namespace Zikula\SettingsBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\SettingsBundle\Event\RoutesNewlyAvailableEvent;
use Zikula\SettingsBundle\Helper\RouteDumperHelper;

class InstallerListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RouteDumperHelper $routeDumperHelper,
        private readonly RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            RoutesNewlyAvailableEvent::class => ['newRoutesAvailable', 5]
        ];
    }

    public function newRoutesAvailable(RoutesNewlyAvailableEvent $event): void
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

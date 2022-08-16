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

namespace Zikula\RoutesModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\ExtensionsModule\Event\ExtensionPostCacheRebuildEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostUpgradeEvent;
use Zikula\RoutesModule\Event\RoutesNewlyAvailableEvent;
use Zikula\RoutesModule\Helper\MultilingualRoutingHelper;
use Zikula\RoutesModule\Helper\RouteDumperHelper;

/**
 * Event handler implementation class for module installer events.
 */
class InstallerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ExtensionPostCacheRebuildEvent::class => ['extensionPostInstalled', 5],
            ExtensionPostUpgradeEvent::class => ['extensionUpgraded', 5],
            ExtensionPostRemoveEvent::class => ['extensionRemoved', 5],
            RoutesNewlyAvailableEvent::class => ['newRoutesAvail', 5]
        ];
    }

    public function __construct(
        private readonly CacheClearer $cacheClearer,
        private readonly RouteDumperHelper $routeDumperHelper,
        private readonly MultilingualRoutingHelper $multilingualRoutingHelper,
        private readonly RequestStack $requestStack
    ) {
    }

    public function extensionPostInstalled(ExtensionPostCacheRebuildEvent $event): void
    {
        $extension = $event->getExtensionBundle();
        if (null === $extension) {
            return;
        }

        if ('ZikulaRoutesModule' === $extension->getName()) {
            // Reload multilingual routing settings.
            $this->multilingualRoutingHelper->reloadMultilingualRoutingSettings();
        }

        $this->cacheClearer->clear('symfony.routing');

        // reload **all** JS routes
        $this->updateJsRoutes();
    }

    public function extensionUpgraded(ExtensionPostUpgradeEvent $event): void
    {
        $extension = $event->getExtensionBundle();
        if (null === $extension) {
            return;
        }

        $this->cacheClearer->clear('symfony.routing');

        // reload **all** JS routes
        $this->updateJsRoutes();
    }

    public function extensionRemoved(ExtensionPostRemoveEvent $event): void
    {
        $extension = $event->getExtensionBundle();
        if (null === $extension || 'ZikulaRoutesModule' === $extension->getName()) {
            return;
        }

        // reload **all** JS routes
        $this->updateJsRoutes();

        $this->cacheClearer->clear('symfony.routing');
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

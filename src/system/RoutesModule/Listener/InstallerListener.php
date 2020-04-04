<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Listener;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostCacheRebuildEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostUpgradeEvent;
use Zikula\RoutesModule\Entity\Factory\EntityFactory;
use Zikula\RoutesModule\Helper\MultilingualRoutingHelper;
use Zikula\RoutesModule\Helper\RouteDumperHelper;
use Zikula\RoutesModule\Listener\Base\AbstractInstallerListener;

/**
 * Event handler implementation class for module installer events.
 */
class InstallerListener extends AbstractInstallerListener
{
    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var RouteDumperHelper
     */
    private $routeDumperHelper;

    /**
     * @var MultilingualRoutingHelper
     */
    private $multilingualRoutingHelper;

    /**
     * @var EntityFactory
     */
    private $entityFactory;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    public static function getSubscribedEvents()
    {
        // override subscription to ALL available events to only needed events.
        return [
            ExtensionPostCacheRebuildEvent::class => ['extensionPostInstalled', 5],
            ExtensionPostUpgradeEvent::class => ['extensionUpgraded', 5],
            ExtensionPostRemoveEvent::class => ['extensionRemoved', 5],
            'new.routes.avail' => ['newRoutesAvail', 5]
        ];
    }

    public function __construct(
        CacheClearer $cacheClearer,
        RouteDumperHelper $routeDumperHelper,
        MultilingualRoutingHelper $multilingualRoutingHelper,
        EntityFactory $entityFactory,
        RequestStack $requestStack
    ) {
        $this->cacheClearer = $cacheClearer;
        $this->routeDumperHelper = $routeDumperHelper;
        $this->multilingualRoutingHelper = $multilingualRoutingHelper;
        $this->entityFactory = $entityFactory;
        $this->requestStack = $requestStack;
    }

    public function extensionPostInstalled(ExtensionPostCacheRebuildEvent $event): void
    {
        parent::extensionPostInstalled($event);

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
        parent::extensionUpgraded($event);

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
        parent::extensionRemoved($event);

        $extension = $event->getExtensionBundle();
        if (null === $extension || 'ZikulaRoutesModule' === $extension->getName()) {
            return;
        }

        // delete any custom routes for the removed bundle
        $this->entityFactory->getRepository('route')->deleteByBundle($extension->getName());

        // reload **all** JS routes
        $this->updateJsRoutes();

        $this->cacheClearer->clear('symfony.routing');
    }

    public function newRoutesAvail(GenericEvent $event): void
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

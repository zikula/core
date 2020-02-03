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
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;
use Zikula\ExtensionsModule\ExtensionEvents;
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
            ExtensionEvents::EXTENSION_POSTINSTALL => ['extensionPostInstalled', 5],
            ExtensionEvents::EXTENSION_UPGRADE => ['extensionUpgraded', 5],
            ExtensionEvents::EXTENSION_REMOVE => ['extensionRemoved', 5],
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

    public function extensionPostInstalled(ExtensionStateEvent $event): void
    {
        parent::extensionPostInstalled($event);

        $extension = $event->getExtension();
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

    public function extensionUpgraded(ExtensionStateEvent $event): void
    {
        parent::extensionUpgraded($event);

        $extension = $event->getExtension();
        if (null === $extension) {
            return;
        }

        $this->cacheClearer->clear('symfony.routing');

        // reload **all** JS routes
        $this->updateJsRoutes();
    }

    public function extensionRemoved(ExtensionStateEvent $event): void
    {
        parent::extensionRemoved($event);

        $extension = $event->getExtension();
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

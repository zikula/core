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
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
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
            CoreEvents::MODULE_POSTINSTALL => ['modulePostInstalled', 5],
            CoreEvents::MODULE_UPGRADE => ['moduleUpgraded', 5],
            CoreEvents::MODULE_REMOVE => ['moduleRemoved', 5],
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

    public function modulePostInstalled(ModuleStateEvent $event): void
    {
        parent::modulePostInstalled($event);

        $module = $event->getModule();
        if (null === $module) {
            return;
        }

        if ('ZikulaRoutesModule' === $module->getName()) {
            // Reload multilingual routing settings.
            $this->multilingualRoutingHelper->reloadMultilingualRoutingSettings();
        }

        $this->cacheClearer->clear('symfony.routing');

        // reload **all** JS routes
        $this->updateJsRoutes();
    }

    public function moduleUpgraded(ModuleStateEvent $event): void
    {
        parent::moduleUpgraded($event);

        $module = $event->getModule();
        if (null === $module) {
            return;
        }

        $this->cacheClearer->clear('symfony.routing');

        // reload **all** JS routes
        $this->updateJsRoutes();
    }

    public function moduleRemoved(ModuleStateEvent $event): void
    {
        parent::moduleRemoved($event);

        $module = $event->getModule();
        if (null === $module || 'ZikulaRoutesModule' === $module->getName()) {
            return;
        }

        // delete any custom routes for the removed bundle
        $this->entityFactory->getRepository('route')->deleteByBundle($module->getName());

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

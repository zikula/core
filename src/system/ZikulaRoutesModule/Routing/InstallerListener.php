<?php

namespace Zikula\RoutesModule\Routing;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouteCollection;
use Zikula\Core\AbstractModule;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\Bundle\CoreBundle\CacheClearer;

/**
 * Class InstallerListener.
 */
class InstallerListener implements EventSubscriberInterface
{
    private $em;

    private $routeFinder;

    private $cacheClearer;

    function __construct(EntityManagerInterface $em, RouteFinder $routeFinder, CacheClearer $cacheClearer)
    {
        $this->em = $em;
        $this->routeFinder = $routeFinder;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            CoreEvents::MODULE_INSTALL => array('installed'),
            CoreEvents::MODULE_UPGRADE => array('upgraded'),
            CoreEvents::MODULE_REMOVE => array('uninstalled')
        );
    }

    /**
     * @param ModuleStateEvent $event
     *
     * @return void
     */
    public function installed(ModuleStateEvent $event)
    {
        $module = $event->getModule();
        if ($module === null) {
            return;
        }

        if ($module->getName() === 'ZikulaRoutesModule') {
            // The module itself just got installed, reload all routes.
            $this->em->getRepository('ZikulaRoutesModule:RouteEntity')->reloadAllRoutes();
            // Reload multilingual routing settings.
            \ModUtil::apiFunc('ZikulaRoutesModule', 'admin', 'reloadMultilingualRoutingSettings');
        } else {
            $this->addRoutesToCache($module);
        }

        $this->cacheClearer->clear('symfony.routing');
    }

    /**
     * @param ModuleStateEvent $event
     *
     * @return void
     */
    public function upgraded(ModuleStateEvent $event)
    {
        $module = $event->getModule();
        if ($module === null) {
            return;
        }

        $this->removeRoutesFromCache($module);
        $this->addRoutesToCache($module);

        $this->cacheClearer->clear('symfony.routing');
    }

    /**
     * @param ModuleStateEvent $event
     *
     * @return void
     */
    public function uninstalled(ModuleStateEvent $event)
    {
        $module = $event->getModule();
        if ($module === null || $module->getName() == 'ZikulaRoutesModule') {
            return;
        }

        $this->removeRoutesFromCache($module);

        $this->cacheClearer->clear('symfony.routing');
    }

    /**
     * Add the specified routes to the cache.
     *
     * @param RouteCollection $routes
     */
    private function addRoutesToCache(AbstractModule $module)
    {
        $routeCollection = $this->routeFinder->find($module);

        if ($routeCollection->count() > 0) {
            $this->em->getRepository('ZikulaRoutesModule:RouteEntity')->addRouteCollection($module, $routeCollection);
        }
    }

    /**
     * Remove all routes of the specified module from cache.
     *
     * @param AbstractModule $module
     */
    private function removeRoutesFromCache(AbstractModule $module)
    {
        $this->em->getRepository('ZikulaRoutesModule:RouteEntity')->removeAllOfModule($module);
    }
} 

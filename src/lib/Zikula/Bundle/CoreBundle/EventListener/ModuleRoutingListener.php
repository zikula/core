<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Cmf\Component\Routing\Event\Events as CmfRoutingEvents;
use Symfony\Cmf\Component\Routing\Event\RouterMatchEvent;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Zikula\Bundle\CoreBundle\Routing\RouteProvider;
use Zikula\Bundle\CoreBundle\Routing\RoutesDumper;
use Zikula\Core\AbstractModule;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;

class ModuleRoutingListener implements EventSubscriberInterface
{
    private $logger;

    private $loader;

    private $em;

    private $kernel;

    private $routeProvider;

    private $cacheFile;

    function __construct(KernelInterface $kernel, EntityManagerInterface $em, Loader $loader, LoggerInterface $logger, RouteProvider $routeProvider)
    {
        $this->kernel = $kernel;
        $this->em = $em;
        $this->logger = $logger;
        $this->loader = $loader;
        $this->routeProvider = $routeProvider;
    }

    public function setCachePath($path, $prefix, $class)
    {
        $this->cacheFile = "{$path}/{$prefix}{$class}.php";
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            CoreEvents::MODULE_INSTALL => array('installed'),
            CoreEvents::MODULE_UPGRADE => array('upgraded'),
            CoreEvents::MODULE_REMOVE => array('uninstalled'),
            CmfRoutingEvents::PRE_DYNAMIC_MATCH_REQUEST => array('preMatch'),
        );
    }

    /**
     * Regenerate routes file if it does not exist.
     *
     * @param RouterMatchEvent $event
     *
     * @return void
     */
    public function preMatch(RouterMatchEvent $event)
    {
        unset($event);

        $fs = new Filesystem();
        if (!$fs->exists($this->cacheFile)) {
            // Cache has been cleared, regenerate file.
            $modules = $this->kernel->getModules();
            foreach ($modules as $module) {
                $this->addRoutesToCache($this->loadRoutes($module));
            }
        }
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

        $routeCollection = $this->loadRoutes($module);
        if ($routeCollection !== false) {
            $this->addRoutesToCache($routeCollection);
        }
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

        $routeCollection = $this->loadRoutes($module);
        if ($routeCollection !== false) {
            $this->updateRoutesOfCache($routeCollection);
        }
    }

    /**
     * @param ModuleStateEvent $event
     *
     * @return void
     */
    public function uninstalled(ModuleStateEvent $event)
    {
        $module = $event->getModule();
        if ($module === null) {
            return;
        }

        $this->removeRoutesFromCache($module);
    }

    /**
     * Load routes of the specified module from the module's configuration file.
     *
     * @param AbstractModule $module
     *
     * @return RouteCollection
     */
    private function loadRoutes(AbstractModule $module)
    {
        try {
            $path = $this->kernel->locateResource($module->getRoutingConfig());
        } catch (\InvalidArgumentException $e) {
            // Routing file does not exist.
            return new RouteCollection();
        }
        $modname = $module->getName();

        $this->logger->info('Loading routes for ' . $modname);

        /** @var RouteCollection $moduleCollection */
        $moduleCollection = $this->loader->import($path);

        $routes = new RouteCollection();

        /** @var \Symfony\Component\Routing\Route $route */
        foreach ($moduleCollection as $name => $route) {
            $route->setOption('module', $modname);
            $name = explode('_', $name);
            $type = $name[count($name) - 2];
            $func = $name[count($name) - 1];
            $route->setOption('type', $type);
            $route->setOption('func', $func);

            $routes->add(strtolower($modname) . "_$type" . "_$func", $route);
        }

        return $routes;
    }

    /**
     * Add the specified routes to the cache.
     *
     * @param RouteCollection $routes
     */
    private function addRoutesToCache(RouteCollection $routeCollection)
    {
        $oldRoutes = $this->routeProvider->getRoutes();
        $oldRoutes->addCollection($routeCollection);

        $this->dump($oldRoutes);
    }

    /**
     * Update the specified routes of the cache.
     *
     * @param RouteCollection $routes
     */
    private function updateRoutesOfCache(RouteCollection $routeCollection)
    {
        $oldRoutes = $this->routeProvider->getRoutes();
        foreach ($routeCollection as $name => $route) {
            $oldRoutes->remove($name);
            $oldRoutes->add($name, $route);
        }

        $this->dump($oldRoutes);
    }

    /**
     * Remove all routes of the specified module from cache.
     *
     * @param AbstractModule $module
     */
    private function removeRoutesFromCache(AbstractModule $module)
    {
        $oldRoutes = $this->routeProvider->getRoutes();
        /** @var Route $route */
        foreach ($oldRoutes as $name => $route) {
            if ($route->getOption('module') == $module->getName()) {
               $oldRoutes->remove($name);
            }
        }

        $this->dump($oldRoutes);
    }

    /**
     * Write routes to file.
     *
     * @param RouteCollection $routeCollection
     */
    private function dump(RouteCollection $routeCollection)
    {
        $dumper = new RoutesDumper();
        $fs = new Filesystem();
        $fs->dumpFile($this->cacheFile, $dumper->dump($routeCollection));
    }
}
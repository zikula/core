<?php

namespace Zikula\RoutesModule\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;
use Zikula\Core\AbstractModule;
use Zikula\RoutesModule\Routing\Util as RoutingUtil;

/**
 * Class RouteFinder.
 */
class RouteFinder
{
    private $logger;

    private $loader;

    private $kernel;

    function __construct(KernelInterface $kernel, Loader $loader, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->loader = $loader;
    }

    /**
     * Load routes of the specified module from the module's configuration file.
     *
     * @param AbstractModule $module
     *
     * @return RouteCollection
     */
    public function find(AbstractModule $module)
    {
        try {
            $path = $this->kernel->locateResource($module->getRoutingConfig());
        } catch (\InvalidArgumentException $e) {
            // Routing file does not exist (e.g. because the bundle could not be located)
            return new RouteCollection();
        }
        $modname = $module->getName();
        $this->logger->info('Loading routes for ' . $modname);

        return $this->loader->import($path);
    }
} 

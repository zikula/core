<?php

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Bridge\DependencyInjection\PhpDumper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Config\ConfigCache;
use Zikula\Core\AbstractBundle;
use Zikula\Core\AbstractModule;
use Zikula\Core\AbstractTheme;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

abstract class ZikulaKernel extends Kernel
{
    /**
     * @var boolean
     */
    private $dump = true;

    /**
     * @var array
     */
    private $modules = array();

    /**
     * @var array
     */
    private $moduleMap = array();

    /**
     * @var array
     */
    private $themes = array();

    /**
     * @var array
     */
    private $themeMap = array();

    /**
     * @var ClassLoader
     */
    private $autoloader;

    /**
     * Flag determines if container is dumped or not
     *
     * @param $flag
     */
    public function setDump($flag)
    {
        $this->dump = $flag;
    }

    public function __construct($env, $debug)
    {
        parent::__construct($env, $debug);

        // this is all to be deprecated (todo drak)
        $paths = array(
            $this->rootDir .'/../config/config.php',
            $this->rootDir.'/../config/personal_config.php',
            $this->rootDir.'/../config/multisites_config.php',
        );

        foreach ($paths as $path) {
            if (is_readable($path)) {
                include $path;
            }
        }
    }

    public function boot()
    {
        if (null === $this->autoloader) {
            $this->getAutoloader();
        }

        parent::boot();


        foreach ($this->bundles as $bundle) {
            if ($bundle instanceof AbstractModule) {
                $this->modules[] = $bundle;
            } elseif ($bundle instanceof AbstractTheme) {
                $this->themes[] = $bundle;
            }
        }

        foreach ($this->bundleMap as $name => $bundles) {
            if ($bundles[0] instanceof AbstractModule) {
                $this->moduleMap[$name] = $bundles;
            } elseif ($bundles[0] instanceof AbstractTheme) {
                $this->themeMap[$name] = $bundles;
            }
        }
    }

    /**
     * Get named module bundle.
     *
     * @param string  $moduleName
     * @param boolean $first
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     * @return \Zikula\Core\AbstractModule|\Zikula\Core\AbstractModule[]
     */
    public function getModule($moduleName, $first = true)
    {
        if (!isset($this->moduleMap[$moduleName])) {
            throw new \InvalidArgumentException(sprintf('Module "%s" does not exist or it is not enabled.', $moduleName, get_class($this)));
        }

        if (true === $first) {
            return $this->moduleMap[$moduleName][0];
        }

        return $this->moduleMap[$moduleName];
    }

    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Get named theme bundle.
     *
     * @param string  $themeName
     * @param boolean $first
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     *
     * @return \Zikula\Core\AbstractTheme|\Zikula\Core\AbstractTheme[]
     */
    public function getTheme($themeName, $first = true)
    {
        if (!isset($this->themeMap[$themeName])) {
            throw new \InvalidArgumentException(sprintf('Theme "%s" does not exist or it is not enabled.', $themeName, get_class($this)));
        }

        if (true === $first) {
            return $this->themeMap[$themeName][0];
        }

        return $this->themeMap[$themeName];
    }

    public function getThemes()
    {
        return $this->themes;
    }

    public function getJustBundles()
    {
        $bundles = array();
        foreach ($this->bundles as $bundle) {
            if (!$bundle instanceof AbstractBundle) {
                $bundles[] = $bundle;
            }
        }

        return $bundles;
    }

    public function setAutoloader(ClassLoader $autoloader)
    {
        $this->autoloader = $autoloader;
    }

    public function getAutoloader()
    {
        if (null === $this->autoloader) {
            $loaders = spl_autoload_functions();
            if ($loaders[0][0] instanceof DebugClassLoader) {
                $this->autoloader = $loaders[0][0]->getClassLoader();
            } else {
                $this->autoloader = $loaders[0][0];
            }
        }

        return $this->autoloader;
    }

    public function getConnectionConfig()
    {
        $dir = is_readable($dir = $this->rootDir.'/config/custom_parameters.yml') ? $dir : $this->rootDir.'/config/parameters.yml';

        return Yaml::parse(file_get_contents($dir));
    }

    public function isClassInBundle($class)
    {
        /* @var BundleInterface $bundle */
        foreach ($this->getBundles() as $bundle) {
            if (0 === strpos($class, $bundle->getNamespace())) {
                return $bundle instanceof AbstractBundle;
            }
        }

        return false;
    }

    public function isClassInActiveBundle($class)
    {
        /* @var AbstractBundle $bundle */
        foreach ($this->getBundles() as $bundle) {
            if (0 === strpos($class, $bundle->getNamespace())) {
                if ($bundle->getState() == AbstractBundle::STATE_ACTIVE) {
                    return true;
                } elseif (!method_exists($bundle, 'getState')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Initializes the data structures related to the bundle management.
     *
     *  - the bundles property maps a bundle name to the bundle instance,
     *  - the bundleMap property maps a bundle name to the bundle inheritance hierarchy (most derived bundle first).
     *
     * @throws \LogicException if two bundles share a common name
     * @throws \LogicException if a bundle tries to extend a non-registered bundle
     * @throws \LogicException if a bundle tries to extend itself
     * @throws \LogicException if two bundles extend the same ancestor
     */
    protected function initializeBundles()
    {
        // init bundles
        $this->bundles = array();
        $topMostBundles = array();
        $directChildren = array();

        foreach ($this->registerBundles() as $bundle) {
            $name = $bundle->getName();
            if (isset($this->bundles[$name])) {
                throw new \LogicException(sprintf('Trying to register two bundles with the same name "%s"', $name));
            }
            $this->bundles[$name] = $bundle;

            if ($parentName = $bundle->getParent()) {
                if (isset($directChildren[$parentName])) {
                    throw new \LogicException(sprintf('Bundle "%s" is directly extended by two bundles "%s" and "%s".', $parentName, $name, $directChildren[$parentName]));
                }
                if ($parentName == $name) {
                    throw new \LogicException(sprintf('Bundle "%s" can not extend itself.', $name));
                }
                $directChildren[$parentName] = $name;
            } else {
                $topMostBundles[$name] = $bundle;
            }
        }

        // look for orphans
        if (count($diff = array_values(array_diff(array_keys($directChildren), array_keys($this->bundles))))) {
            throw new \LogicException(sprintf('Bundle "%s" extends bundle "%s", which is not registered.', $directChildren[$diff[0]], $diff[0]));
        }

        // inheritance
        $this->bundleMap = array();
        foreach ($topMostBundles as $name => $bundle) {
            $bundleMap = array($bundle);
            $hierarchy = array($name);

            while (isset($directChildren[$name])) {
                $name = $directChildren[$name];
                array_unshift($bundleMap, $this->bundles[$name]);
                $hierarchy[] = $name;
            }

            foreach ($hierarchy as $bundle) {
                $this->bundleMap[$bundle] = $bundleMap;
                array_pop($bundleMap);
            }
        }
    }

    /**
     * Initializes the service container.
     *
     * The cached version of the service container is used when fresh, otherwise the
     * container is built.
     *
     * Overridden not to dump the container.
     */
    protected function initializeContainer()
    {
        if (true === $this->dump) {
            return parent::initializeContainer();
        }

        $this->container = $this->buildContainer();
        $this->container->set('kernel', $this);
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param ConfigCache      $cache     The config cache
     * @param ContainerBuilder $container The service container
     * @param string           $class     The name of the class to generate
     * @param string           $baseClass The name of the container's base class
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        // cache the container
        $dumper = new PhpDumper($container);
        $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));
        if (!$this->debug) {
            $content = self::stripComments($content);
        }

        $cache->write($content, $container->getResources());
    }

    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     *
     * Allows container to build services after being dumped and frozen
     *
     * @return string
     */
    protected function getContainerBaseClass()
    {
        return 'Zikula_ServiceManager';
        //return 'Symfony\Component\DependencyInjection\Container';
        // return 'Zikula\Bridge\DependencyInjection\ContainerBuilder';
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        return new \Zikula_ServiceManager(new ParameterBag($this->getKernelParameters()));
        //return new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
    }

    /**
     * Gets the environment parameters.
     *
     * Only the parameters starting with "ZIKULA__" are considered.
     *
     * @return array An array of parameters
     */
    protected function getEnvParameters()
    {
        $parameters = parent::getEnvParameters();
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'ZIKULA__')) {
                $parameters[strtolower(str_replace('__', '.', substr($key, 9)))] = $value;
            }
        }

        return $parameters;
    }
}

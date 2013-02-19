<?php

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Bridge\DependencyInjection\PhpDumper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Config\ConfigCache;

abstract class ZikulaKernel extends Kernel
{
    /**
     * @var boolean
     */
    private $dump = true;

    /**
     * Flag determines if container is dumped or not
     *
     * @param $flag
     */
    public function setDump($flag)
    {
        $this->dump = $flag;
    }

    /**
     * Overridden to prevent error-reporting being overridden
     */
    public function init()
    {
        // todo - switch out Zikula's error reporting for Sf
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
        //return 'Zikula\Bridge\DependencyInjection\ContainerBuilder';
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

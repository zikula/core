<?php

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Symfony\Component\HttpKernel\Kernel;
use Zikula\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

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
        //return 'Zikula\Component\DependencyInjection\ContainerBuilder';
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
}

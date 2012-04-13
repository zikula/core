<?php

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Symfony\Component\HttpKernel\Kernel;
use Zikula\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

abstract class ZikulaKernel extends Kernel
{
    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     *
     * @return string
     */
    protected function getContainerBaseClass()
    {
        return '\Zikula\Component\DependencyInjection\ContainerBuilder';
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        return new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
    }
}

<?php

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Symfony\Component\HttpKernel\Kernel;

abstract class ZikulaKernel extends Kernel
{
//    public function __construct($environment, $debug)
//    {
//        parent::__construct($environment, $debug);
//    }

    public function compile()
    {
        parent::compile();

        $this->parameterBag->resolve();
    }

    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     *
     * @return string
     */
    protected function getContainerBaseClass()
    {
        return '\Symfony\Component\DependencyInjection\ContainerBuilder';
    }
}

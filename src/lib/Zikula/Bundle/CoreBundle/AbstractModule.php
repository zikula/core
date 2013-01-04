<?php

namespace Zikula\Bundle\CoreBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractModule extends Bundle
{
    private $serviceIds = array();

//    abstract public function getVersion();

//    /**
//     * @return ModuleInstallerInterface
//     */
//    abstract public function createInstaller();

    public function build(ContainerBuilder $container)
    {
        // modules have to use DI Extensions
    }

//    public function getContainerExtension()
//    {
//        $ex = parent::getContainerExtension();
//
//        if ($ex != null) {
//            $ex = new DependencyInjection\SandboxContainerExtension($ex, $this->serviceIds);
//        }
//
//        return $ex;
//    }

    public function getServiceIds()
    {
        return $this->serviceIds;
    }
}

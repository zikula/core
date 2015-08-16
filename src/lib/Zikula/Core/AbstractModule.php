<?php

namespace Zikula\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractModule extends AbstractBundle
{
    private $serviceIds = array();

    public function getNameType()
    {
        return 'Module';
    }

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
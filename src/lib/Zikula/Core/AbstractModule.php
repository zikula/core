<?php

namespace Zikula\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractModule extends Bundle
{
    private $serviceIds = array();

//    abstract public function getVersion();
//    public function getVersion()
//    {
//        $ns = $this->getNamespace();
//        $class = $ns.'\\'.substr($ns, strrpos($ns, '\\')+1, strlen($ns)).'Version';
//
//        $version = new $class;
//        $version['name'] = $this->getName();
//
//        return $version;
//    }

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
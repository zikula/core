<?php

namespace Zikula\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractModule extends AbstractBundle
{
    private $serviceIds = array();

    protected function getNameType()
    {
        return 'Module';
    }
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

    public function getInstallerClass()
    {
        $ns = $this->getNamespace();
        $class = $ns.'\\'.substr($ns, strrpos($ns, '\\')+1, strlen($ns)).'Installer';

        return $class;
    }

    public function getVersionClass()
    {
        $ns = $this->getNamespace();
        $class = $ns.'\\'.substr($ns, strrpos($ns, '\\')+1, strlen($ns)).'Version';

        return $class;
    }

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
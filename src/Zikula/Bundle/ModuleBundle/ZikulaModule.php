<?php

namespace Zikula\ModuleBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class ZikulaModule extends Bundle
{
    private $serviceIds = array();

    public function __construct()
    {
        $name = get_class($this);
        $posNamespaceSeperator = strrpos($name, '\\');
        $this->name = substr($name, $posNamespaceSeperator + 1);
    }

    public abstract function getVersion();

    /**
     * @return ModuleInstallerInterface
     */
    public abstract function createInstaller();

    final public function build(ContainerBuilder $container)
    {
        // modules have to use DI Extensions
    }

    final public function getContainerExtension()
    {
        $ex = parent::getContainerExtension();

        if ($ex != null) {
            $ex = new DependencyInjection\SandboxContainerExtension($ex, $this->serviceIds);
        }

        return $ex;
    }

    public function getServiceIds()
    {
        return $this->serviceIds;
    }
}
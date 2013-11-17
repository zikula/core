<?php

namespace Zikula\Bundle\ModuleBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractModule extends Bundle
{
    protected static $staticPath;
    private $serviceIds = array();

    public function __construct()
    {
        $name = get_class($this);
        $posNamespaceSeperator = strrpos($name, '\\');
        $this->name = substr($name, $posNamespaceSeperator + 1);
    }

    /**
     * Gets the base path of the module.
     *
     * @return string Base path of the final child class
     */
    public static function getStaticPath()
    {
        if (null !== self::$staticPath) {
            return self::$staticPath;
        }

        $reflection = new \ReflectionClass(get_called_class());
        self::$staticPath = dirname($reflection->getFileName());

        return self::$staticPath;
    }

//    abstract public function getVersion();

//    /**
//     * @return ModuleInstallerInterface
//     */
//    abstract public function createInstaller();

    public function build(ContainerBuilder $container)
    {
        // modules have to use DI Extensions
    }

    public function getContainerExtension()
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
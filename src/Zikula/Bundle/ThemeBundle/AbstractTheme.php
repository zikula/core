<?php

namespace Zikula\Bundle\ThemeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractTheme extends Bundle
{
    protected static $staticPath;

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

//    public abstract function getVersion();

    public function getServiceIds()
    {
        return array();
    }
}

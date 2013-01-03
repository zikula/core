<?php

namespace Zikula\Bundle\CoreBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

abstract class Bundle extends BaseBundle
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

    public function getNameType()
    {
        return 'Bundle';
    }

    public function hasCommands()
    {
        return false;
    }

    public function getContainerExtension()
    {
        $type = $this->getNameType();
        $typeLower = strtolower($type);
        if (null === $this->extension) {
            $basename = preg_replace('/'.$type.'/', '', $this->getName());

            $class = $this->getNamespace().'\\DependencyInjection\\'.$basename.'Extension';
            if (class_exists($class)) {
                $extension = new $class();

                // check naming convention
                $expectedAlias = Container::underscore($basename);
                if ($expectedAlias != $extension->getAlias()) {
                    throw new \LogicException(sprintf(
                        'The extension alias for the default extension of a %s must be the underscored version of the %s name ("%s" instead of "%s")',
                        $typeLower, $typeLower, $expectedAlias, $extension->getAlias()
                    ));
                }

                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        if ($this->extension) {
            return $this->extension;
        }
    }

    public function registerCommands(Application $application)
    {
        if ($this->hasCommands()) {
            parent::registerCommands($application);
        }
    }
}

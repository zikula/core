<?php

namespace Zikula\Core;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractBundle extends Bundle
{
    protected $booted = false;
    protected static $staticPath;

    private $basePath;

    public function isBooted()
    {
        return $this->booted;
    }

//    public function __construct()
//    {
//        $name = get_class($this);
//        $posNamespaceSeperator = strrpos($name, '\\');
//        $this->name = substr($name, $posNamespaceSeperator + 1);
//    }

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

    /**
     * Gets the translation domain path
     *
     * @return string
     */
    public function getLocalePath()
    {
        return $this->getPath().'/Resources/locale';
    }

    /**
     * Gets the translation domain path
     *
     * @return string
     */
    public function getViewsPath()
    {
        return $this->getPath().'/Resources/views';
    }

    /**
     * @return string
     *
     * @todo remove (drak)
     *
     * @internal This is just required until the transition is over fully to Symfony
     */
    public function getRelativePath()
    {
        $path = str_replace('\\', '/', $this->getPath());
        preg_match('#/(modules|system|themes)/#', $path, $matches);

        return substr($path, strpos($path, $matches[1]), strlen($path));
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

    /**
     * @return string
     */
    public function getBasePath()
    {
        if (null === $this->basePath) {
            $ns = str_replace('\\', '/', $this->getNamespace());
            $path = str_replace('\\', '/', $this->getPath());
            $this->basePath = substr($path, 0, strrpos($path, $ns)-1);
        }

        return $this->basePath;
    }

    protected function getNameType()
    {
        return 'Bundle';
    }

    protected function hasCommands()
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

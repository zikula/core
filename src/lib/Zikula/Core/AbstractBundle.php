<?php

namespace Zikula\Core;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractBundle extends Bundle
{
    const STATE_DISABLED = 2;
    const STATE_ACTIVE = 3;
    const STATE_MISSING = 6;

    protected $state;
    protected $booted = false;

    private $basePath;

    public function isBooted()
    {
        return $this->booted;
    }

    public function setState($state)
    {
        if (!in_array($state, array(self::STATE_ACTIVE, self::STATE_DISABLED))) {
            throw new \InvalidArgumentException(sprintf('Invalid state %s', $state));
        }

        $this->state = $state;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

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

    public function getTranslationDomain()
    {
        return strtolower($this->getName());
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

    /**
     * Get container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;

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
        if (!in_array($state, [self::STATE_ACTIVE, self::STATE_DISABLED, self::STATE_MISSING])) {
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
        $class = $ns.'\\'.substr($ns, strrpos($ns, '\\') + 1, strlen($ns)).'Installer';

        return $class;
    }

    /**
     * @deprecated remove in Core 2.0.0
     * @return string
     */
    public function getVersionClass()
    {
        $ns = $this->getNamespace();
        $class = $ns.'\\'.substr($ns, strrpos($ns, '\\') + 1, strlen($ns)).'Version';

        return $class;
    }

    public function getRoutingConfig()
    {
        return "@{$this->name}/Resources/config/routing.yml";
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
     * Gets the views path
     *
     * @return string
     */
    public function getViewsPath()
    {
        return $this->getPath().'/Resources/views';
    }

    /**
     * Gets the config path.
     *
     * @return string
     */
    public function getConfigPath()
    {
        return $this->getPath().'/Resources/config';
    }

    /**
     * @return string
     *
     * @todo remove (drak)
     * @deprecated This is just a workaround
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
            $this->basePath = substr($path, 0, strrpos($path, $ns) - 1);
        }

        return $this->basePath;
    }

    public function getNameType()
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

    /**
     * @return MetaData
     */
    public function getMetaData()
    {
        $scanner = new Scanner();
        $jsonPath = $this->getPath() . '/composer.json';
        $jsonContent = $scanner->decode($jsonPath);
        $metaData = new MetaData($jsonContent);
        if (!empty($this->container)) {
            $metaData->setTranslator($this->container->get('translator'));
        }
        $metaData->setDirectoryFromBundle($this);
        if (!empty($this->container) && $this->container->getParameter('installed')) {
            // overwrite composer.json settings with dynamic values from extension repository
            $extensionEntity = $this->container->get('zikula_extensions_module.extension_repository')->get($this->getName());
            if (!is_null($extensionEntity)) {
                $metaData->setUrl($extensionEntity->getUrl());
                $metaData->setDisplayName($extensionEntity->getDisplayname());
                $metaData->setDescription($extensionEntity->getDescription());
            }
        }

        return $metaData;
    }
}

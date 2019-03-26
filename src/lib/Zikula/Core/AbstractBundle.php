<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Zikula\Common\Translator\Translator;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository;
use Zikula\ThemeModule\AbstractTheme;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;

abstract class AbstractBundle extends Bundle
{
    const STATE_DISABLED = 2;

    const STATE_ACTIVE = 3;

    const STATE_MISSING = 6;

    protected $state;

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
        $class = $ns . '\\' . mb_substr($ns, mb_strrpos($ns, '\\') + 1, mb_strlen($ns)) . 'Installer';

        return $class;
    }

    public function getRoutingConfig()
    {
        return '@' . $this->name . '/Resources/config/routing.yml';
    }

    public function getTranslationDomain()
    {
        return mb_strtolower($this->getName());
    }

    /**
     * Gets the translation domain path
     *
     * @return string
     */
    public function getLocalePath()
    {
        return $this->getPath() . '/Resources/locale';
    }

    /**
     * Gets the views path
     *
     * @return string
     */
    public function getViewsPath()
    {
        return $this->getPath() . '/Resources/views';
    }

    /**
     * Gets the config path.
     *
     * @return string
     */
    public function getConfigPath()
    {
        return $this->getPath() . '/Resources/config';
    }

    /**
     * Get the assetpath relative to /web e.g. /modules/acmefoo
     * @return string
     */
    public function getRelativeAssetPath()
    {
        return mb_strtolower($this->getNameType() . 's/' . mb_substr($this->getName(), 0, -mb_strlen($this->getNameType())));
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
        $typeLower = mb_strtolower($type);
        if (null === $this->extension) {
            $basename = preg_replace('/' . $type . '/', '', $this->getName());

            $class = $this->getNamespace() . '\\DependencyInjection\\' . $basename . 'Extension';
            if (class_exists($class)) {
                $extension = new $class();

                // check naming convention
                $expectedAlias = Container::underscore($basename);
                if ($expectedAlias !== $extension->getAlias()) {
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
     * Add the bundle's stylesheet to the page assets
     * @param string $name
     */
    public function addStylesheet($name = 'style.css')
    {
        try {
            $styleSheet = $this->getContainer()->get(Asset::class)->resolve('@' . $this->getName() . ":css/${name}");
        } catch (\InvalidArgumentException $e) {
            $styleSheet = '';
        }
        if (!empty($styleSheet)) {
            $weight = $this instanceof AbstractTheme ? AssetBag::WEIGHT_THEME_STYLESHEET : AssetBag::WEIGHT_DEFAULT;
            $this->container->get('zikula_core.common.theme.assets_css')->add([$styleSheet => $weight]);
        }
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
            $metaData->setTranslator($this->container->get(Translator::class));
        }
        if (!empty($this->container) && $this->container->getParameter('installed')) {
            // overwrite composer.json settings with dynamic values from extension repository
            $extensionEntity = $this->container->get(ExtensionRepository::class)->get($this->getName());
            if (!is_null($extensionEntity)) {
                $metaData->setUrl($extensionEntity->getUrl());
                $metaData->setDisplayName($extensionEntity->getDisplayname());
                $metaData->setDescription($extensionEntity->getDescription());
            }
        }

        return $metaData;
    }
}

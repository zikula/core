<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule;

use InvalidArgumentException;
use LogicException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use function Symfony\Component\String\s;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\Composer\Scanner;
use Zikula\ExtensionsModule\Helper\MetaDataHelper;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;

abstract class AbstractExtension extends Bundle
{
    public function getInstallerClass(): string
    {
        $ns = $this->getNamespace();
        $installerName = s($ns)->afterLast('\\')->append('Installer');
        $class = $ns . '\\' . $installerName->toString();

        return $class;
    }

    public function getRoutingConfig(): string
    {
        return '@' . $this->name . '/Resources/config/routing.yaml';
    }

    /**
     * Gets the translation path.
     */
    public function getLocalePath(): string
    {
        return $this->getPath() . '/Resources/locale';
    }

    public function getViewsPath(): string
    {
        return $this->getPath() . '/Resources/views';
    }

    public function getConfigPath(): string
    {
        return $this->getPath() . '/Resources/config';
    }

    /**
     * Get the asset path relative to /public e.g. /modules/acmefoo.
     */
    public function getRelativeAssetPath(): string
    {
        $folder = $this->getNameType() . 's/';
        $name = s($this->getName())->trimEnd($this->getNameType());

        return s($folder . $name)->lower()->toString();
    }

    public function getNameType(): string
    {
        return 'Bundle';
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $type = $this->getNameType();
            $typeLower = mb_strtolower($type);
            $basename = preg_replace('/' . $type . '/', '', $this->getName());

            $class = $this->getNamespace() . '\\DependencyInjection\\' . $basename . 'Extension';
            if (class_exists($class)) {
                $extension = new $class();

                // check naming convention
                $expectedAlias = Container::underscore($basename);
                if ($expectedAlias !== $extension->getAlias()) {
                    throw new LogicException(sprintf('The extension alias for the default extension of a %s must be the underscored version of the %s name ("%s" instead of "%s")', $typeLower, $typeLower, $expectedAlias, $extension->getAlias()));
                }

                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        return $this->extension ?: null;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Add the bundle's stylesheet to the page assets.
     */
    public function addStylesheet(string $name = 'style.css'): void
    {
        try {
            $styleSheet = $this->getContainer()->get(Asset::class)->resolve('@' . $this->getName() . ":css/${name}");
        } catch (InvalidArgumentException $exception) {
            $styleSheet = '';
        }
        if (!empty($styleSheet)) {
            $weight = $this instanceof AbstractTheme ? AssetBag::WEIGHT_THEME_STYLESHEET : AssetBag::WEIGHT_DEFAULT;
            $this->container->get('zikula_core.common.theme.assets_css')->add([$styleSheet => $weight]);
        }
    }

    public function getMetaData(): MetaData
    {
        $scanner = new Scanner();
        $jsonPath = $this->getPath() . '/composer.json';
        $jsonContent = $scanner->decode($jsonPath);
        $metaData = new MetaData($jsonContent);
        if (!empty($this->container) && $this->container->has('translator')) {
            $metaData->setTranslator($this->container->get('translator'));
        }
        if (!empty($this->container) && $this->container->has(MetaDataHelper::class)) {
            $metaData = $this->container->get(MetaDataHelper::class)->setDynamicMetaData($metaData);
        }

        return $metaData;
    }
}

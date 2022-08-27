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

namespace Zikula\Bundle\CoreBundle;

use InvalidArgumentException;
use LogicException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use function Symfony\Component\String\s;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\Composer\Scanner;
use Zikula\ThemeBundle\Engine\Asset;
use Zikula\ThemeBundle\Engine\AssetBag;

abstract class AbstractExtension extends Bundle
{
    public function getConfigPath(): string
    {
        return $this->getPath() . '/Resources/config';
    }

    /**
     * Get the asset path relative to /public e.g. /bundles/acmefoo.
     */
    public function getRelativeAssetPath(): string
    {
        $folder = $this->getNameType() . 's/';
        $name = s($this->getName())->beforeLast($this->getNameType());

        return s($folder . $name)->lower()->toString();
    }

    public function getNameType(): string
    {
        return 'Bundle';
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $baseName = $this->getName();
            $type = $this->getNameType();
            if (str_ends_with($baseName, $type)) {
                $baseName = mb_substr($baseName, 0, -1 * mb_strlen($type));
            }

            $class = $this->getNamespace() . '\\DependencyInjection\\' . $baseName . 'Extension';
            if (class_exists($class)) {
                $extension = new $class();

                // check naming convention
                $expectedAlias = Container::underscore($baseName);
                if ($expectedAlias !== $extension->getAlias()) {
                    $typeLower = mb_strtolower($type);

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

    public function getMetaData(): MetaData
    {
        $scanner = new Scanner();
        $jsonPath = $this->getPath() . '/composer.json';
        $jsonContent = $scanner->decode($jsonPath);
        $metaData = new MetaData($jsonContent);
        if (!empty($this->container) && $this->container->has('translator')) {
            $metaData->setTranslator($this->container->get('translator'));
        }

        return $metaData;
    }
}

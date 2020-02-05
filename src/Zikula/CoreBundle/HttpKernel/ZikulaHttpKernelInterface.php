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

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use InvalidArgumentException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\RebootableInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Zikula\ExtensionsModule\AbstractModule;
use Zikula\ExtensionsModule\AbstractTheme;

interface ZikulaHttpKernelInterface extends KernelInterface, TerminableInterface, RebootableInterface
{
    /**
     * Gets named module bundle.
     *
     * @throws InvalidArgumentException when the bundle is not enabled
     */
    public function getModule(string $moduleName): AbstractModule;

    public function getModules(): array;

    /**
     * Checks if name is is the list of core extensions.
     */
    public static function isCoreExtension(string $extensionName): bool;

    /**
     * Gets named theme bundle.
     *
     * @throws InvalidArgumentException when the bundle is not enabled
     */
    public function getTheme(string $themeName): AbstractTheme;

    public function getThemes(): array;

    public function getJustBundles(): array;

    /**
     * Is this a Bundle?
     */
    public function isBundle(string $name): bool;

    public function setAutoloader(callable $autoloader): void;

    public function getAutoloader(): object;

    public function isClassInBundle(string $class): bool;
}

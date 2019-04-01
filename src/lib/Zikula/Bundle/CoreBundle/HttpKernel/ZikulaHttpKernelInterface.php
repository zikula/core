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

use Composer\Autoload\ClassLoader;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Zikula\Core\AbstractModule;
use Zikula\ThemeModule\AbstractTheme;

interface ZikulaHttpKernelInterface extends KernelInterface, TerminableInterface
{
    /**
     * Flag determines if container is dumped or not.
     */
    public function setDump(bool $flag): void;

    /**
     * Gets named module bundle.
     *
     * @throws InvalidArgumentException when the bundle is not enabled
     */
    public function getModule(string $moduleName): AbstractModule;

    public function getModules(): array;

    /**
     * Checks if name is is the list of core modules.
     */
    public static function isCoreModule(string $moduleName): bool;

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

    public function setAutoloader(ClassLoader $autoloader): void;

    public function getAutoloader(): ClassLoader;

    public function getConnectionConfig(): array;

    public function isClassInBundle(string $class): bool;
}

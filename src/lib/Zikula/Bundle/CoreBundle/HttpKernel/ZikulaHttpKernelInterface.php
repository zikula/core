<?php

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
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Zikula\Core\AbstractModule;
use Zikula\ThemeModule\AbstractTheme;

interface ZikulaHttpKernelInterface extends KernelInterface, TerminableInterface
{
    /**
     * Flag determines if container is dumped or not
     *
     * @param $flag
     */
    public function setDump($flag);

    /**
     * Gets named module bundle.
     *
     * @param string $moduleName
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     * @return AbstractModule
     */
    public function getModule($moduleName);

    public function getModules();

    /**
     * Checks if name is is the list of core modules.
     * @param $moduleName
     * @return bool
     */
    public static function isCoreModule($moduleName);

    /**
     * Gets named theme bundle.
     *
     * @param string $themeName
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     *
     * @return AbstractTheme
     */
    public function getTheme($themeName);

    public function getThemes();

    public function getJustBundles();

    /**
     * Is this a Bundle?
     *
     * @param $name
     * @return bool
     */
    public function isBundle($name);

    public function setAutoloader(ClassLoader $autoloader);

    public function getAutoloader();

    public function getConnectionConfig();

    public function isClassInBundle($class);
}

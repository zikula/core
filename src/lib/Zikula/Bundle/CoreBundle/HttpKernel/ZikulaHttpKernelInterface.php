<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\HttpKernel;

use Composer\Autoload\ClassLoader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
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
     * Get named module bundle.
     *
     * @param string  $moduleName
     * @param boolean $first
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     * @return \Zikula\Core\AbstractModule|\Zikula\Core\AbstractModule[]
     */
    public function getModule($moduleName, $first = true);

    public function getModules();

    /**
     * Checks if name is is the list of core modules.
     * @param $moduleName
     * @return bool
     */
    public static function isCoreModule($moduleName);

    /**
     * Get named theme bundle.
     *
     * @param string  $themeName
     * @param boolean $first
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     *
     * @return AbstractTheme|AbstractTheme
     */
    public function getTheme($themeName, $first = true);

    public function getThemes();

    public function getJustBundles();

    /**
     * Is this a Bundle?
     *
     * @param $name
     * @param bool $first
     * @return bool
     */
    public function isBundle($name, $first = true);

    public function setAutoloader(ClassLoader $autoloader);

    public function getAutoloader();

    public function getConnectionConfig();

    public function isClassInBundle($class);
}

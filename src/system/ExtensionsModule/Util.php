<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule;

use LogUtil;
use Zikula_AbstractVersion;
use ModUtil;
use System;
use Zikula\Core\AbstractModule;

/**
 * Helper functions for the extensions module
 */
class Util
{
    /**
     * Get version metadata for a module.
     * @deprecated remove at Core-2.0 (simply use `$bundle->getMetaData()`)
     *
     * @param string $moduleName Module Name
     * @param string $rootdir    Root directory of the module (default: modules)
     * @param \Zikula\Core\AbstractModule|null $module injected bundle
     *
     * @throws \InvalidArgumentException Thrown if the version information cannot be obtained for the requested module or
     *                                          if the version class isn't of the correct type or
     *                                          if the lib directory cannot be found for v1.3 style modules
     *
     * @return Zikula_AbstractVersion|\Zikula\Bundle\CoreBundle\Bundle\MetaData|array
     */
    public static function getVersionMeta($moduleName, $rootdir = 'modules', $module = null)
    {
        $modversion = [];
        if (null === $module) {
            $module = ModUtil::getModule($moduleName);
        }
        $class = null === $module ? "{$moduleName}_Version" : $module->getVersionClass();

        if (class_exists($class)) {
            try {
                $modversion = new $class($module);
            } catch (\Exception $e) {
                LogUtil::log(__f('%1$s threw an exception reporting: "%2$s"', [$class, $e->getMessage()]), \Monolog\Logger::CRITICAL);
                throw new \InvalidArgumentException(__f('%1$s threw an exception reporting: "%2$s"', [$class, $e->getMessage()]), 0, $e);
            }
            if (!$modversion instanceof Zikula_AbstractVersion) {
                throw new \InvalidArgumentException(__f('%s is not an instance of Zikula_AbstractVersion', get_class($modversion)));
            }
        } elseif ($module instanceof AbstractModule) {
            // Core-2.0 spec
            $modversion = $module->getMetaData();
        } elseif (!is_dir("$rootdir/$moduleName")) {
            $modversion = [
                'name' => $moduleName,
                'description' => '',
                'version' => 0
            ];
        } elseif (is_dir("$rootdir/$moduleName/lib")) {
            throw new \InvalidArgumentException(__f('Could not find %1$s for module %2$s', ["{$moduleName}_Version", $moduleName]));
        } else {
            // pre 1.3 modules
            $legacyVersionPath = "$rootdir/$moduleName/pnversion.php";
            if (!file_exists($legacyVersionPath)) {
                //                if (!System::isUpgrading()) {
                //                    LogUtil::log(__f("Error! Could not load the file '%s'.", $legacyVersionPath), \Monolog\Logger::CRITICAL);
                //                    throw new \InvalidArgumentException(__f("Error! Could not load the file '%s'.", $legacyVersionPath));
                //                }
                //                $modversion = [
                //                    'name' => $moduleName,
                //                    'description' => '',
                //                    'version' => 0
                //                ];
                return [];
            }

            include $legacyVersionPath;
        }

        return $modversion;
    }
}

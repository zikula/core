<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Extensions_Util
{
    /**
     * Get version metadata for a module.
     *
     * @param string $moduleName Module Name.
     * @param string $rootdir    Root directory of the module (default: modules).
     *
     * @return Zikula_AbstractVersion|array
     */
    public static function getVersionMeta($moduleName, $rootdir = 'modules')
    {
        $modversion = array();

        $class = "{$moduleName}_Version";
        if (class_exists($class)) {
            try {
                $modversion = new $class();
            } catch (Exception $e) {
                LogUtil::log(__f('%1$s threw an exception reporting: "%2$s"', array($class, $e->getMessage())), Zikula_AbstractErrorHandler::CRIT);
                throw new InvalidArgumentException(__f('%1$s threw an exception reporting: "%2$s"', array($class, $e->getMessage())), 0, $e);
            }
            if (!$modversion instanceof Zikula_AbstractVersion) {
                LogUtil::registerError(__f('%s is not an instance of Zikula_AbstractVersion', get_class($modversion)));
            }
        } elseif (!is_dir("$rootdir/$moduleName")) {
            $modversion = array(
                    'name' => $moduleName,
                    'description' => '',
                    'version' => 0
                );
        } elseif (is_dir("$rootdir/$moduleName/lib")) {
            LogUtil::registerError(__f('Could not find %1$s for module %2$s', array("{$moduleName}_Version", $moduleName)));
        } else {
            // pre 1.3 modules
            $legacyVersionPath = "$rootdir/$moduleName/pnversion.php";
            if (!file_exists($legacyVersionPath)) {
                if (!System::isUpgrading()) {
                    LogUtil::log(__f("Error! Could not load the file '%s'.", $legacyVersionPath), Zikula_AbstractErrorHandler::CRIT);
                    LogUtil::registerError(__f("Error! Could not load the file '%s'.", $legacyVersionPath));
                }
                $modversion = array(
                    'name' => $moduleName,
                    'description' => '',
                    'version' => 0
                );
            } else {
                include $legacyVersionPath;
            }
        }

        return $modversion;
    }
}

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

class Modules_Util
{
    /**
     * Get version metadata for a module.
     *
     * @param string $moduleName        Module Name.
     * @param string $legacyVersionPath Path to legacy version file (default empty).
     *
     * @return Zikula_Version|array
     */
    public static function getVersionMeta($moduleName, $legacyVersionPath = '')
    {
        $modversion = array();
        $class = "{$moduleName}_Version";
        if (class_exists($class)) {
            $modversion = new $class();
            if (!$modversion instanceof Zikula_Version) {
                LogUtil::registerError(__f('%s is not an instance of Zikula_Version', get_class($modversion)));
            }
        } elseif (is_dir("modules/$moduleName/lib") || is_dir("system/$moduleName/lib")) {
            LogUtil::registerError(__f('Could not find %1$s for module %2$s', array("{$moduleName}_Version", $moduleName)));
        } else {
            if (!file_exists($legacyVersionPath)) {
                LogUtil::registerError(__f('Cannot %1$s for module %2$s', array($legacyVersionPath, $moduleName)));
            } else {
                include $legacyVersionPath;
            }
        }

        return $modversion;
    }
}
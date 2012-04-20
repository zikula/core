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

namespace ExtensionsModule;

use LogUtil, System;
use Zikula_AbstractErrorHandler;
use Zikula_AbstractVersion;

class Util
{
    /**
     * Get version metadata for a module.
     *
     * @param string $moduleName Module Name.
     *
     * @return Zikula_AbstractVersion|array
     */
    public static function getVersionMeta($moduleName)
    {
        $modversion = array();

        $class = "{$moduleName}\Version";
        if (class_exists($class)) {
            try {
                $modversion = new $class();
            } catch (\Exception $e) {
                LogUtil::log(__f('%1$s threw an exception reporting: "%2$s"', array($class, $e->getMessage())), Zikula_AbstractErrorHandler::CRIT);
                throw new \InvalidArgumentException(__f('%1$s threw an exception reporting: "%2$s"', array($class,
                    $e->getMessage())), 0, $e);
            }
            if (!$modversion instanceof \Zikula_AbstractVersion) {
                LogUtil::registerError(__f('%s is not an instance of Zikula\Framework\AbstractVersion',
                    get_class($modversion)));
            }
        } else {
            LogUtil::registerError(__f('Could not find %1$s for module %2$s', array("{$moduleName}\Version",
                $moduleName)));
        }

        return $modversion;
    }
}

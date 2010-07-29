<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * ServiceUtil
 *
 * In the context of Zikula, unfortunately we need to maintain the ServiceManager
 * since it's not convenient to pass around using dependency injection.
 */
class ServiceUtil
{
    /**
     * Service manager instance.
     *
     * @var Zikula_ServiceManager
     */
    private static $serviceManager;

    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Get manager instance.
     *
     * @param Zikula_Core $core Core instance (optional).
     *
     * @return Zikula_ServiceManager
     */
    public static function getManager(Zikula_Core $core = null)
    {
        if (self::$serviceManager) {
            return self::$serviceManager;
        }

        self::$serviceManager = $core->getServiceManager();
        return self::$serviceManager;
    }
}

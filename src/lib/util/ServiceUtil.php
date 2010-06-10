<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
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
    private static $serviceManager;

    private function __construct()
    {
    }

    public static function getManager()
    {
        if (self::$serviceManager) {
            return self::$serviceManager;
        }

        // this looks strange, but it's deliberate (for IDEs to see API - drak)
        $serviceManager = new Zikula_ServiceManager('zikula.servicemanager');
        self::$serviceManager = $serviceManager;

        return $serviceManager;
    }
}

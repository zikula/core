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
     * Service handlers key for persistence.
     */
    const HANDLERS = '/ServiceHandlers';

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

    /**
     * Convenience shortcut to get a service.
     *
     * @param string $id Service name.
     *
     * @return object
     */
    public static function getService($id)
    {
        return self::getManager()->getService($id);
    }

    /**
     * Register a persistent service handler.
     *
     * This will be loaded into ServiceManager at runtime.
     *
     * @param string                           $id         Service ID.
     * @param Zikula_ServiceManager_Definition $definition Class definition.
     * @param boolean                          $shared     Shared service or not.
     *
     * @return void
     */
    public static function registerPersistentService($id, Zikula_ServiceManager_Definition $definition, $shared=true)
    {
        $handlers = ModUtil::getVar(self::HANDLERS, 'definitions', array());
        $handlers[$id] = array('definition' => $definition, 'shared' => $shared);
        ModUtil::setVar(self::HANDLERS, 'definitions', $handlers);
    }

    /**
     * Un-register a persistent service handler.
     *
     * This will be loaded into ServiceManager at runtime.
     *
     * @param string $id Service ID.
     *
     * @return void
     */
    public static function unRegisterPersistentService($id)
    {
        $handlers = ModUtil::getVar(self::HANDLERS, 'definitions', false);
        if (!$handlers) {
            return;
        }

        if (array_key_exists($id, $handlers)) {
            unset($handlers[$id]);
        }

        ModUtil::setVar(self::HANDLERS, 'definitions', $handlers);
    }

    /**
     * Load all persisted services into ServiceManager.
     *
     * @return void
     */
    public static function loadPersistentServices()
    {
        $handlers = ModUtil::getVar(self::HANDLERS, 'definitions', array());
        if (!$handlers) {
            return;
        }

        foreach ($handlers as $id => $handler) {
            self::$serviceManager->registerService(new Zikula_ServiceManager_Service($id, $handler['definition'], $handler['shared']));
        }
    }
}

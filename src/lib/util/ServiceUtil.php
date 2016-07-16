<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param Zikula_Core $core Core instance (optional)
     *
     * @return Zikula_ServiceManager
     */
    public static function getManager(Zikula_Core $core = null)
    {
        if (self::$serviceManager) {
            return self::$serviceManager;
        }

        self::$serviceManager = (isset($core)) ? $core->getContainer() : null;

        return self::$serviceManager;
    }

    public static function hasContainer()
    {
        return self::$serviceManager !== null;
    }

    public static function setContainer(ContainerInterface $container)
    {
        self::$serviceManager = $container;
    }

    /**
     * Convenience shortcut to get a service.
     *
     * @param string $id Service name
     *
     * @deprecated since 1.4.0
     * @see get()
     *
     * @return object
     */
    public static function getService($id)
    {
        return self::getManager()->get($id);
    }

    /**
     * Convenience shortcut to get a service.
     *
     * @param string $id Service name
     *
     * @return object
     */
    public static function get($id)
    {
        return self::getManager()->get($id);
    }

    /**
     * Register a persistent service handler.
     *
     * This will be loaded into ServiceManager at runtime.
     *
     * @param string                           $id         Service ID
     * @param Zikula_ServiceManager_Definition $definition Class definition
     * @param boolean                          $shared     Shared service or not
     *
     * @return void
     */
    public static function registerPersistentService($id, Zikula_ServiceManager_Definition $definition, $shared = true)
    {
        $handlers = ModUtil::getVar(self::HANDLERS, 'definitions', []);
        $handlers[$id] = [
            'definition' => $definition,
            'shared' => $shared
        ];
        ModUtil::setVar(self::HANDLERS, 'definitions', $handlers);
    }

    /**
     * Un-register a persistent service handler.
     *
     * This will be loaded into ServiceManager at runtime.
     *
     * @param string $id Service ID
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
        $handlers = ModUtil::getVar(self::HANDLERS, 'definitions', []);
        if (!$handlers) {
            return;
        }

        foreach ($handlers as $id => $handler) {
            self::$serviceManager->registerService($id, $handler['definition'], $handler['shared']);
        }
    }
}

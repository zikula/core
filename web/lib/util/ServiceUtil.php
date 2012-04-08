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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Zikula\Core\Core;

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
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private static $container;

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
     * @param Core $core Core instance (optional).
     *
     * @return ContainerBuilder
     */
    public static function getManager(Core $core = null)
    {
        if (self::$container) {
            return self::$container;
        }

        self::$container = $core->getContainer();

        return self::$container;
    }

    /**
     * Convenience shortcut to get a service.
     *
     * @param string $id Service name.
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
     * @param string     $id         Service ID.
     * @param Definition $definition Class definition.
     * @param boolean    $shared     Shared service or not.
     *
     * @return void
     */
    public static function registerPersistentService($id, Definition $definition, $shared=true)
    {
        $handlers = ModUtil::getVar(self::HANDLERS, 'definitions', array());
        if ($shared) {
            $definition->setScope(ContainerInterface::SCOPE_CONTAINER);
        } else {
            $definition->setScope(ContainerInterface::SCOPE_PROTOTYPE);
        }
        
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
            if ($handler['shared']) {
                $handler['definition']->setScope(ContainerInterface::SCOPE_CONTAINER);
            } else {
                $handler['definition']->setScope(ContainerInterface::SCOPE_PROTOTYPE);
            }

            self::$container->setDefinition($id, $handler['definition']);
        }
    }
}

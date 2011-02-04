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
 * EventUtil
 */
class EventUtil
{
    /**
     * Singleton instance of EventManager.
     *
     * @var object
     */
    public static $eventManager;

    /**
     * Event handlers key for persistence.
     */
    const HANDLERS = '/EventHandlers';

    /**
     * Singleton constructor.
     */
    private function __construct()
    {
    }

    /**
     * Get EventManager instance.
     *
     * @param Zikula_Core $core Core instance.
     *
     * @return Zikula_EventManager
     */
    static public function getManager(Zikula_Core $core = null)
    {
        if (self::$eventManager) {
            return self::$eventManager;
        }

        self::$eventManager = $core->getEventManager();

        return self::$eventManager;
    }

    /**
     * Notify event.
     *
     * @param Zikula_Event $event Event.
     *
     * @return Zikula_Event
     */
    static public function notify(Zikula_Event $event)
    {
        return self::getManager()->notify($event);
    }

    /**
     * NotifyUntil event.
     *
     * @param Zikula_Event $event Event.
     *
     * @return Zikula_Event
     */
    static public function notifyUntil(Zikula_Event $event)
    {
        return self::getManager()->notifyUntil($event);
    }

    /**
     * Attach listener.
     *
     * @param string       $name    Name of event.
     * @param array|string $handler PHP Callable.
     *
     * @return void
     */
    static public function attach($name, $handler)
    {
        self::getManager()->attach($name, $handler);
    }

    /**
     * Detach listener.
     *
     * @param string       $name    Name of listener.
     * @param array|string $handler PHP callable.
     *
     * @return void
     */
    static public function detach($name, $handler)
    {
        self::getManager()->detach($name, $handler);
    }

    /**
     * Loader for custom handlers.
     *
     * @param string $dir Path to the folder holding the eventhandler classes.
     *
     * @return void
     */
    static public function attachCustomHandlers($dir)
    {
        static $loaded;
        static $classes;

        $dir = realpath($dir);

        if (isset($loaded[$dir])) {
            return;
        }

        $serviceManager = ServiceUtil::getManager();

        $it = FileUtil::getFiles($dir, false, false, 'php', 'f');

        foreach ($it as $file) {
            $before = get_declared_classes();
            include realpath($file);
            $after = get_declared_classes();

            $diff = new ArrayIterator(array_diff($after, $before));
            if (count($diff) > 1) {
                while ($diff->valid()) {
                    $className = $diff->current();
                    $diff->next();
                }
            } else {
                $className = $diff->current();
            }

            if (!isset($classes[$className])) {
                self::attachEventHandler($className, $serviceManager);
                $classes[$className] = true;
            }
        }

        $loaded[$dir] = true;
    }

    /**
     * Load and attach handlers for Zikula_EventHandler listeners.
     *
     * Loads event handlers that extend Zikula_EventHandler
     *
     * @param string                $className      The name of the class.
     * @param Zikula_ServiceManager $serviceManager The service manager instance (optional).
     *
     * @throws LogicException If class is not instance of Zikula_EventHandler.
     *
     * @return void
     */
    public static function attachEventHandler($className, Zikula_ServiceManager $serviceManager = null)
    {
        if (!$serviceManager) {
            $serviceManager = ServiceUtil::getManager();
        }

        $r = new ReflectionClass($className);
        $handler = $r->newInstance($serviceManager);

        if (!$handler instanceof Zikula_EventHandler) {
            throw new LogicException(sprintf('Class %s must be an instance of Zikula_EventHandler', $className));
        }

        $handler->setup();
        $handler->attach();
    }

    /**
     * Register a persistent event for a module.
     *
     * @param string   $moduleName Module name.
     * @param string   $eventName  Event name.
     * @param callable $callable   PHP static callable or instance of Zikula_ServiceHandler.
     * @param integer  $weight     Weight of handler, default = 10.
     *
     * @throws InvalidArgumentException If the callable given is not callable.
     *
     * @return void
     */
    public static function registerPersistentModuleHandler($moduleName, $eventName, $callable, $weight=10)
    {
        if (!is_callable($callable) && !$callable instanceof Zikula_ServiceHandler) {
            throw new InvalidArgumentException('$callable is not a valid PHP callable or instance of Zikula_ServiceHandler');
        }
        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, array());
        $handlers[] = array('eventname' => $eventName, 'callable' => $callable, 'weight' => $weight);
        ModUtil::setVar(self::HANDLERS, $moduleName, $handlers);
    }

    /**
     * Unregister a single persistent event handler for a module.
     *
     * @param string   $moduleName Module name.
     * @param string   $eventName  Event name.
     * @param callable $callable   PHP callable. No instanciated callables allowed.
     *
     * @return void
     */
    public static function unregisterPersistentModuleHandler($moduleName, $eventName, $callable)
    {
        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, false);
        if (!$handlers) {
            return;
        }
        $filteredHandlers = array();
        foreach ($handlers as $handler) {
            if ($handler !== array('eventname' => $eventName, 'callable' => $callable)) {
                $filteredHandlers[] = $handler;
            }
        }
        ModUtil::setVar(self::HANDLERS, $moduleName, $filteredHandlers);
    }

    /**
     * Unregister all persisten event handlers for a given module.
     *
     * @param string $moduleName Module name.
     *
     * @return void
     */
    public static function unregisterPersistentModuleHandlers($moduleName)
    {
        ModUtil::delVar(self::HANDLERS, $moduleName);
    }

    /**
     * Register a persistent plugin handler.
     *
     * @param string   $moduleName Module name.
     * @param string   $pluginName Plugin name.
     * @param string   $eventName  Event name.
     * @param callable $callable   PHP static callable or instance of Zikula_ServiceHandler.
     * @param integer  $weight     Weight of handler, default = 10.
     *
     * @throws InvalidArgumentException If callable is not callable.
     *
     * @return void
     */
    public static function registerPersistentPluginHandler($moduleName, $pluginName, $eventName, $callable, $weight=10)
    {
        if (!is_callable($callable) && !$callable instanceof Zikula_ServiceHandler) {
            throw new InvalidArgumentException('$callable is not a valid PHP callable or instance of Zikula_ServiceHandler');
        }
        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, array());
        $handlers[] = array('plugin' => $pluginName, 'eventname' => $eventName, 'callable' => $callable, 'weight' => 10);
        ModUtil::setVar(self::HANDLERS, $moduleName, $handlers);
    }

    /**
     * Unregister a single event handler for a given module plugin.
     *
     * @param string   $moduleName Module name.
     * @param string   $pluginName Plugin name.
     * @param string   $eventName  Event name.
     * @param callable $callable   PHP callable. No instanciated callables allowed.
     *
     * @return void
     */
    public static function unregisterPersistentPluginHandler($moduleName, $pluginName, $eventName, $callable)
    {
        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, false);
        if (!$handlers) {
            return;
        }
        $filteredHandlers = array();
        foreach ($handlers as $handler) {
            if ($handler !== array('plugin' => $pluginName, 'eventname' => $eventName, 'callable' => $callable)) {
                $filteredHandlers[] = $handler;
            }
        }
        ModUtil::setVar(self::HANDLERS, $moduleName, $filteredHandlers);
    }

    /**
     * Unregister all persistent events handlers for a given module plugin.
     *
     * @param string $moduleName Module name.
     * @param string $pluginName Plugin name.
     *
     * @return void
     */
    public static function unregisterPersistentPluginHandlers($moduleName, $pluginName)
    {
        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, false);
        if (!$handlers) {
            return;
        }
        $filteredHandlers = array();
        foreach ($handlers as $handler) {
            if ($handler['plugin'] !== $pluginName) {
                $filteredHandlers[] = $handler;
            }
        }
        ModUtil::setVar(self::HANDLERS, $moduleName, $filteredHandlers);
    }

    /**
     * Load all persistent events handlers into EventManager.
     *
     * This loads persistent events registered by modules and module plugins.
     *
     * @internal
     *
     * @return void
     */
    public static function loadPersistentEvents()
    {
        $handlerGroup = ModUtil::getVar(self::HANDLERS);
        if (!$handlerGroup) {
            return;
        }
        foreach ($handlerGroup as $module => $handlers) {
            if (!$handlers) {
                continue;
            }
            foreach ($handlers as $handler) {
                if (isset($handler['plugin'])) {
                    $className = "{$module}_{$handler[plugin]}_Plugin";
                    $plugin = PluginUtil::loadPlugin($className);
                    if (!$plugin->hasBooted() || !$plugin->isInstalled() || !$plugin->isEnabled()) {
                        // don't attach an event if the plugin is disables
                        continue;
                    }
                }

                if (ModUtil::available($module)) {
                    try {
                        self::attach($handler['eventname'], $handler['callable'], $handler['weight']);
                    } catch (InvalidArgumentException $e) {
                        LogUtil::log(sprintf("Event handler could not be attached because %s", $e->getMessage()), Zikula_ErrorHandler::ERR);
                    }
                }
            }
        }
    }

}

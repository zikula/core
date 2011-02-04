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
     * Register a static persistent event for a module.
     *
     * @param string       $moduleName Module name.
     * @param string       $eventName  Event name.
     * @param string|array $callable   PHP static callable.
     * @param integer      $weight     Weight of handler, default = 10.
     *
     * @throws InvalidArgumentException If the callable given is not callable.
     *
     * @return void
     */
    public static function registerPersistentModuleHandler($moduleName, $eventName, $callable, $weight=10)
    {
        if (!is_callable($callable)) {
            if (is_array($callable)) {
                throw new InvalidArgumentException(sprintf('array(%s, %s) is not a valid PHP callable', $callable[0], $callable[1]));
            }

            throw new InvalidArgumentException(sprintf('%s is not a valid PHP callable', $callable));
        }
        
        if (is_array($callable) && is_object($callable[0])) {
            throw new InvalidArgumentException('Callable cannot be an instanciated class');
        }

        $handlers = ModUtil::getVar(self::HANDLERS, $owner, array());
        $handlers[] = array('eventname' => $eventName, 'callable' => $callable, 'weight' => $weight);
        ModUtil::setVar(self::HANDLERS, $moduleName, $handlers);
    }

    /**
     * Unregister a static persistent event handler for a module.
     *
     * @param string       $moduleName Module name.
     * @param string       $eventName  Event name.
     * @param string|array $callable   PHP static callable.
     * @param integer      $weight     Weight.
     *
     * @return void
     */
    public static function unregisterPersistentModuleHandler($moduleName, $eventName, $callable, $weight=10)
    {
        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, false);
        if (!$handlers) {
            return;
        }
        $filteredHandlers = array();
        foreach ($handlers as $handler) {
            if ($handler !== array('eventname' => $eventName, 'callable' => $callable, 'weight' => $weight)) {
                $filteredHandlers[] = $handler;
            }
        }
        ModUtil::setVar(self::HANDLERS, $moduleName, $filteredHandlers);
    }

    /**
     * Register a Zikula_EventHandler as a persistent handler.
     *
     * @param string  $moduleName Module name.
     * @param string  $className  Class name (subclass of Zikula_EventHandler).
     *
     * @throws InvalidArgumentException If class is not available or not a subclass of Zikula_EventHandler.
     *
     * @return void
     */
    public static function registerPersistentEventHandlerClass($moduleName, $className)
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Class %s does not exist or cannot be found', $className));
        }
        
        $reflection = new ReflectionClass($className);
        if (!$reflection->isSubclassOf('Zikula_EventHandler')) {
            throw new InvalidArgumentException(sprintf('%s is not a subclass of Zikula_EventHandler', $className));
        }

        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, array());
        $handlers[] = array('classname' => $className);
        ModUtil::setVar(self::HANDLERS, $moduleName, $handlers);
    }

    /**
     * Unregister a Zikula_EventHandler event handler.
     *
     * @param string $moduleName Module name.
     * @param string $className  Class name (subclass of Zikula_EventHandler).
     *
     * @return void
     */
    public static function unregisterPersistentStaticHandler($moduleName, $className)
    {
        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, false);
        if (!$handlers) {
            return;
        }
        $filteredHandlers = array();
        foreach ($handlers as $handler) {
            if ($handler !== array('classname' => $className)) {
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
    public static function unregisterPersistentHandlers($moduleName)
    {
        ModUtil::delVar(self::HANDLERS, $moduleName);
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
                if (ModUtil::available($module)) {
                    try {
                        if (isset($handler['classname'])) {
                            foreach ($handlers as $handler) {
                                self::attachEventHandler($handler['classname']);
                            }
                        } else {
                            self::attach($handler['eventname'], $handler['callable'], $handler['weight']);
                        }
                    } catch (InvalidArgumentException $e) {
                        LogUtil::log(sprintf("Event handler could not be attached because %s", $e->getMessage()), Zikula_ErrorHandler::ERR);
                    }
                }
            }
        }
    }

    /**
     * Resolve the correct callable for a handler.
     *
     * @param array $handler Handler.
     *
     * @return array|Zikula_ServiceHandler
     */
    protected static function resolveCallable($handler)
    {
        if ($handler['serviceid']) {
            $callable = new Zikula_ServiceHandler($handler['serviceid'], $handler['method']);
        } else {
            $callable = array($handler['classname'], $handler['method']);
        }

        return $callable;
    }

}

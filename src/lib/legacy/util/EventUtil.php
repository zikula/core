<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\EventDispatcher\Event;

/**
 * EventUtil
 * @deprecated remove at Core-2.0
 */
class EventUtil
{
    /**
     * Singleton instance of EventManager.
     *
     * @var Zikula_EventManager
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
     * @param Zikula_Core $core Core instance
     *
     * @return Zikula_EventManager
     */
    public static function getManager(Zikula_Core $core = null)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        if (self::$eventManager) {
            return self::$eventManager;
        }

        self::$eventManager = $core->getDispatcher();

        return self::$eventManager;
    }

    public static function setManager($manager)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        self::$eventManager = $manager;
    }

    /**
     * Notify event.
     *
     * @param Zikula_Event $event Event
     *
     * @deprecated since 1.4.0
     * @see dispatch()
     *
     * @return Zikula_Event
     */
    public static function notify(Zikula_Event $event)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        return self::getManager()->dispatch($event->getName(), $event);
    }

    /**
     * dispatch event.
     *
     * @param string       $name  Event name
     * @param Zikula_Event $event Event
     *
     * @return Zikula_Event
     */
    public static function dispatch($name, Event $event = null)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        return self::getManager()->dispatch($name, $event);
    }

    /**
     * Attach listener.
     *
     * @param string       $name    Name of event
     * @param array|string $handler PHP Callable
     *
     * @return void
     */
    public static function attach($name, $handler)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        self::getManager()->addListener($name, $handler);
    }

    /**
     * Detach listener.
     *
     * @param string       $name    Name of listener
     * @param array|string $handler PHP callable
     *
     * @return void
     */
    public static function detach($name, $handler)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        self::getManager()->removeListener($name, $handler);
    }

    /**
     * Loader for custom handlers.
     *
     * @param string $dir Path to the folder holding the eventhandler classes
     *
     * @return void
     */
    public static function attachCustomHandlers($dir)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        self::$eventManager->getContainer()->get('zikula')->attachHandlers($dir);
    }

    /**
     * Load and attach handlers for Zikula_AbstractEventHandler listeners.
     *
     * Loads event handlers that extend Zikula_AbstractEventHandler
     *
     * @param string $className The name of the class
     *
     * @return void
     */
    public static function attachEventHandler($className)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        $serviceManager = ServiceUtil::getManager();
        $serviceManager->get('zikula')->attachEventHandler($className);
    }

    /**
     * Register a static persistent event for a module.
     *
     * @param string       $moduleName Module name
     * @param string       $eventName  Event name
     * @param string|array $callable   PHP static callable
     * @param integer      $weight     Weight of handler, default = 10
     *
     * @throws InvalidArgumentException If the callable given is not callable
     *
     * @return void
     *
     * Note: If the exact same handler is already registered, this function does nothing
     */
    public static function registerPersistentModuleHandler($moduleName, $eventName, $callable, $weight = 10)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        if (!is_callable($callable)) {
            if (is_array($callable)) {
                throw new InvalidArgumentException(sprintf('[%s, %s] is not a valid PHP callable', $callable[0], $callable[1]));
            }

            throw new InvalidArgumentException(sprintf('%s is not a valid PHP callable', $callable));
        }

        if (is_array($callable) && is_object($callable[0])) {
            throw new InvalidArgumentException('Callable cannot be an instanciated class');
        }

        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, []);
        $newHandler = [
            'eventname' => $eventName,
            'callable' => $callable,
            'weight' => $weight
        ];
        foreach ($handlers as $handler) {
            if ($handler == $newHandler) {
                // The exact same handler exists already. Do nothing but display a warning.
                if (System::isDevelopmentMode()) {
                    LogUtil::registerWarning(__f('The eventhandler "%1$s" for "%2$s" could not be registered because it is registered already.', [$eventName, $moduleName]));
                } else {
                    $warns = LogUtil::getWarningMessages(false);
                    $msg = __f('The eventhandlers for "%1$s" could not be registered because they are registered already.', [$moduleName]);
                    if (!in_array(DataUtil::formatForDisplayHTML($msg), $warns)) {
                        LogUtil::registerWarning($msg);
                    }
                }

                return;
            }
        }

        $handlers[] = $newHandler;
        ModUtil::setVar(self::HANDLERS, $moduleName, $handlers);
    }

    /**
     * Unregister a static persistent event handler for a module.
     *
     * @param string       $moduleName Module name
     * @param string       $eventName  Event name
     * @param string|array $callable   PHP static callable
     * @param integer      $weight     Weight
     *
     * @return void
     */
    public static function unregisterPersistentModuleHandler($moduleName, $eventName, $callable, $weight = 10)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, false);
        if (!$handlers) {
            return;
        }
        $filteredHandlers = [];
        foreach ($handlers as $handler) {
            if ($handler !== ['eventname' => $eventName, 'callable' => $callable, 'weight' => $weight]) {
                $filteredHandlers[] = $handler;
            }
        }
        ModUtil::setVar(self::HANDLERS, $moduleName, $filteredHandlers);
    }

    /**
     * Register a Zikula_AbstractEventHandler as a persistent handler.
     *
     * @param string $moduleName Module name
     * @param string $className  Class name (subclass of Zikula_AbstractEventHandler)
     *
     * @throws InvalidArgumentException If class is not available or not a subclass of Zikula_AbstractEventHandler
     *
     * @return void
     *
     * Note: If the exact same handler is already registered, this function does nothing
     */
    public static function registerPersistentEventHandlerClass($moduleName, $className)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Class %s does not exist or cannot be found', $className));
        }

        $reflection = new ReflectionClass($className);
        if (!$reflection->isSubclassOf('Zikula_AbstractEventHandler')) {
            throw new InvalidArgumentException(sprintf('%s is not a subclass of Zikula_AbstractEventHandler', $className));
        }

        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, []);
        $newHandler = ['classname' => $className];
        foreach ($handlers as $handler) {
            if ($handler == $newHandler) {
                // The exact same handler exists already. Do nothing but display a warning.
                if (System::isDevelopmentMode()) {
                    LogUtil::registerWarning(__f('The eventhandler class "%1$s" for "%2$s" could not be registered because it is registered already.', [$className, $moduleName]));
                } else {
                    $warns = LogUtil::getWarningMessages(false);
                    $msg = __f('The eventhandlers for "%1$s" could not be registered because they are registered already.', [$moduleName]);
                    if (!in_array(DataUtil::formatForDisplayHTML($msg), $warns)) {
                        LogUtil::registerWarning($msg);
                    }
                }

                return;
            }
        }

        $handlers[] = $newHandler;
        ModUtil::setVar(self::HANDLERS, $moduleName, $handlers);
    }

    /**
     * Unregister a Zikula_AbstractEventHandler event handler.
     *
     * @param string $moduleName Module name
     * @param string $className  Class name (subclass of Zikula_AbstractEventHandler)
     *
     * @return void
     */
    public static function unregisterPersistentStaticHandler($moduleName, $className)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, false);
        if (!$handlers) {
            return;
        }
        $filteredHandlers = [];
        foreach ($handlers as $handler) {
            if ($handler !== ['classname' => $className]) {
                $filteredHandlers[] = $handler;
            }
        }
        ModUtil::setVar(self::HANDLERS, $moduleName, $filteredHandlers);
    }

    /**
     * Unregister all persisten event handlers for a given module.
     *
     * @param string $moduleName Module name
     *
     * @return void
     */
    public static function unregisterPersistentModuleHandlers($moduleName)
    {
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

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
        @trigger_error('EventUtil is deprecated, please use Symfony events instead.', E_USER_DEPRECATED);

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
                            if (class_exists($handler['classname'])) {
                                $reflection = new ReflectionClass($handler['classname']);
                                if ($reflection->isSubclassOf('Zikula_AbstractEventHandler')) {
                                    self::attachEventHandler($handler['classname']);
                                } else {
                                    LogUtil::log(sprintf("Event handler class '%s' was not attached because class is not a subclass of '%s'", $handler['classname'], 'Zikula_AbstractEventHandler'), \Monolog\Logger::ERROR);
                                }
                            }
                        } else {
                            if (is_callable($handler['callable'])) {
                                self::attach($handler['eventname'], $handler['callable'], $handler['weight']);
                            } else {
                                LogUtil::log(sprintf("Event handler was not attached for event '%s' because method '%s' is not callable", $handler['eventname'], $handler['callable'][0].'::'.$handler['callable'][1]), \Monolog\Logger::ERROR);
                            }
                        }
                    } catch (InvalidArgumentException $e) {
                        LogUtil::log(sprintf("Event handler could not be attached because %s", $e->getMessage()), \Monolog\Logger::ERROR);
                    }
                }
            }
        }
    }

    /**
     * Resolve the correct callable for a handler.
     *
     * @param array $handler Handler
     *
     * @return array|Zikula_ServiceHandler
     */
    protected static function resolveCallable($handler)
    {
        if ($handler['serviceid']) {
            $callable = new Zikula_ServiceHandler($handler['serviceid'], $handler['method']);
        } else {
            $callable = [$handler['classname'], $handler['method']];
        }

        return $callable;
    }
}

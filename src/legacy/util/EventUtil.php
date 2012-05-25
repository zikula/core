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

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * EventUtil
 */
class EventUtil
{
    /**
     * Singleton instance of EventDispatcher.
     *
     * @var EventDispatcher
     */
    public static $dispatcher;

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
     * Get EventDispatcher instance.
     *
     * @param EventDispatcherInterface $dispatcher
     *
     * @return EventDispatcherInterface
     */
    public static function getManager(EventDispatcherInterface $dispatcher = null)
    {
        if (self::$dispatcher) {
            return self::$dispatcher;
        }

        if (null === $dispatcher) {
            throw new \InvalidArgumentException('No event dispatcher was specified or previously loaded');
        }

        self::$dispatcher = $dispatcher;

        return self::$dispatcher;
    }

    /**
     * Dispatch event.
     *
     * @param string $name  Event name.
     * @param Event  $event Event.
     *
     * @return Event
     */
    public static function dispatch($name, Event $event = null)
    {
        return self::getManager()->dispatch($name, $event);
    }

    /**
     * Loader for custom handlers.
     *
     * @param string $dir Path to the folder holding the eventhandler classes.
     *
     * @return void
     */
    public static function attachCustomHandlers($dir)
    {
        self::$dispatcher->getContainer()->get('zikula')->attachHandlers($dir);
    }

    /**
     * Load and attach handlers for Zikula\Framework\AbstractEventHandler listeners.
     *
     * Loads event handlers that extend Zikula\Framework\AbstractEventHandler
     *
     * @param string $className The name of the class.
     *
     * @return void
     */
    public static function attachEventHandler($className)
    {
        $container = ServiceUtil::getManager();
        $container->get('zikula')->attachEventHandler($className);
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

        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, array());
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
     * Register a Zikula\Framework\AbstractEventHandler as a persistent handler.
     *
     * @param string $moduleName Module name.
     * @param string $className  Class name (subclass of Zikula\Framework\AbstractEventHandler).
     *
     * @throws InvalidArgumentException If class is not available or not a subclass of Zikula\Framework\AbstractEventHandler.
     *
     * @return void
     */
    public static function registerPersistentEventHandlerClass($moduleName, $className)
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Class %s does not exist or cannot be found', $className));
        }

        $reflection = new ReflectionClass($className);
        if (!$reflection->isSubclassOf('Zikula\Framework\AbstractEventHandler')) {
            throw new InvalidArgumentException(sprintf('%s is not a subclass of Zikula\Framework\AbstractEventHandler', $className));
        }

        $handlers = ModUtil::getVar(self::HANDLERS, $moduleName, array());
        $handlers[] = array('classname' => $className);
        ModUtil::setVar(self::HANDLERS, $moduleName, $handlers);
    }

    /**
     * Unregister a Zikula\Framework\AbstractEventHandler event handler.
     *
     * @param string $moduleName Module name.
     * @param string $className  Class name (subclass of Zikula\Framework\AbstractEventHandler).
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
    public static function unregisterPersistentModuleHandlers($moduleName)
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
                            self::attachEventHandler($handler['classname']);
                        } else {
                            self::$dispatcher->addListener($handler['eventname'], $handler['callable'], $handler['weight']);
                        }
                    } catch (InvalidArgumentException $e) {
                        LogUtil::log(sprintf("Event handler could not be attached because %s", $e->getMessage()), Zikula_AbstractErrorHandler::ERR);
                    }
                }
            }
        }
    }
}

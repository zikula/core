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
     * Singleton constructor.
     */
    private function __construct()
    {
    }

    /**
     * Get EventManager instance.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager instance.
     *
     * @return Zikula_EventManager
     */
    static public function getManager(Zikula_ServiceManager $serviceManager = null)
    {
        if (self::$eventManager) {
            return self::$eventManager;
        }

        self::$eventManager = new Zikula_EventManager($serviceManager);
        $serviceManager->attachService('zikula.eventmanager', self::$eventManager);

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
        return self::getManager()->notify($event);
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
     * @throws LogicException If the created handler isn't a Zikula_Event.
     * @return void
     */
    static public function attachCustomHandlers($dir = null)
    {
        static $loaded;

        $dir = (is_null($dir) ? 'config' . DIRECTORY_SEPARATOR . 'EventHandlers' : $dir);

        if (isset($loaded[$dir])) {
            return;
        }

        $eventManager = self::getManager();
        $serviceManager = ServiceUtil::getManager();

        $it = FileUtil::getFiles($dir, false, false, 'php', 'f');

        foreach ($it as $file) {
            $before = get_declared_classes();
            include realpath($file);
            $after  = get_declared_classes();

            $diff = new ArrayIterator(array_diff($after, $before));
            if (count($diff) > 1) {
                while ($diff->valid()) {
                    $className = $diff->current();
                    $diff->next();
                }
            } else {
                $className = $diff->current();
            }

            $r = new ReflectionClass($className);
            $handler = $r->newInstanceArgs(array($eventManager, $serviceManager));

            if (!$handler instanceof Zikula_EventHandler) {
                throw new LogicException(sprintf('Class %s must be an instance of Zikula_EventHandler', $className));
            }
            $handler->attach();
        }

        $loaded[$dir] = true;
    }
}

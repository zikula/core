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
 * EventManagerUtil
 */
class EventManagerUtil
{
    /**
     * Singleton instance of EventManager.
     *
     * @var object
     */
    public static $eventManagerInstance;

    /**
     * Singleton constructor.
     */
    private function __construct()
    {
    }

    /**
     * Get EventManager instance.
     *
     * @return object EventManager.
     */
    static public function getEventManager()
    {
        if (!self::$eventManagerInstance) {
            self::$eventManagerInstance = new EventManager();
        }

        return self::$eventManagerInstance;
    }

    /**
     * Notify event.
     *
     * @param Event $event Event.
     */
    static public function notify(Event $event)
    {
        return self::getEventManager()->notify($event);
    }

    /**
     * NotifyUntil event.
     *
     * @param Event $event Event.
     */
    static public function notifyUntil(Event $event)
    {
        return self::getEventManager()->notify($event);
    }

    /**
     * Attach listener.
     *
     * @param string $name Name of event.
     * @param array|string $handler PHP Callable.
     */
    static public function attach($name, $handler)
    {
        self::getEventManager()->attach($name, $handler);
    }

    /**
     * Detach listener.
     *
     * @param string       $name    Name of listener.
     * @param array|string $handler PHP callable.
     */
    static public function detach($name, $handler)
    {
        self::getEventManager()->detach($name);
    }

    /**
     * Loader for custom handlers.
     *
     * @param string $dir Path to the folder holding the eventhandler classes.
     */
    static public function attachCustomHandlers($dir = null)
    {
        static $loaded;

        $dir = (is_null($dir) ? 'config' . DIRECTORY_SEPARATOR . 'EventHandlers' : $dir);

        if (isset($loaded[$dir])) {
            return;
        }

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

            $handler = new $className;
            if (!$handler instanceof CustomEventHandler) {
                throw new LogicException(sprintf('Class %s must be an instance of CustomEventHandler', $className));
            }
            $handler->attach();
        }

        $loaded[$dir] = true;
    }
}

<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package EventManager
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
        $dir = (is_null($dir) ? 'config' . DIRECTORY_SEPARATOR . 'EventHandlers' : $dir);
        $it = FileUtil::getFiles($dir);
        foreach ($it as $file) {
            if (!strrpos($file, '.php')) {
                continue;
            }
            include $dir . DIRECTORY_SEPARATOR . $file;
            $class = substr($file, 0, strrpos($file, '.php'));
            $handler = new $class;
            $handler->attach();
        }
    }
}

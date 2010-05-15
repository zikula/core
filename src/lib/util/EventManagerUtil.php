<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
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
    public static $eventManagerInstance;

    private function __construct()
    {
    }

    static public function getEventManager()
    {
        if (!self::$eventManagerInstance) {
            self::$eventManagerInstance = new EventManager();
        }

        return self::$eventManagerInstance;
    }

    static public function notify(Event $event)
    {
        self::getEventManager()->notify($event);
    }

    static public function notifyUntil(Event $event)
    {
        self::getEventManager()->notify($event);
    }

    static public function attach($name, $handler)
    {
        self::getEventManager()->attach($name, $handler);
    }

    static public function detach($name, $handler)
    {
        self::getEventManager()->detach($name);
    }
}
<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Zikula\Core\Event\GenericEvent;

/**
 * SessionUtil
 */
class SessionUtil
{
    /**
     * Session required.
     *
     * Starts a session or terminates loading.
     *
     * @return void
     */
    public static function requireSession()
    {
        $event = new GenericEvent('session.require');
        EventUtil::getManager()->notify($event);
    }
    /**
     * Let session expire nicely
     *
     * @return void
     */
    public static function expire()
    {
        $storage = ServiceUtil::getService('session.storage');
        $storage->expire();
    }

    /**
     * Check if a session has expired or not
     *
     * @return boolean
     */
    public static function hasExpired()
    {
        $storage = ServiceUtil::getService('session.storage');
        return $storage->isExpired();
    }

    /**
     * Regerate session id.
     *
     * @param boolean $force Force regeneration, default: false.
     *
     * @return void
     */
    public static function regenerate($force = false)
    {
        $storage = ServiceUtil::getService('session');
        $storage->migrate();
    }

    /**
     * Regenerate session according to probability set by admin.
     *
     * @return void
     */
    public static function random_regenerate()
    {
        if (!System::getVar('sessionrandregenerate')) {
            return;
        }

        $chance = 100 - System::getVar('sessionregeneratefreq');
        $a = mt_rand(0, $chance);
        $b = mt_rand(0, $chance);
        if ($a == $b) {
            self::regenerate();
        }
    }

    /**
     * Define the name of our session cookie.
     *
     * @access private
     * @return string
     */
    public static function getCookieName()
    {
        // Include number of dots in session name such that we use a different session for
        // www.domain.xx and domain.xx. Otherwise we run into problems with both cookies for
        // www.domain.xx as well as domain.xx being sent to www.domain.xx simultaneously!
        $hostNameDotCount = substr_count(System::getHost(), '.');
        return System::getVar('sessionname') . $hostNameDotCount;
    }
}

<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * SessionUtil
 * @deprecated remove at Core-2.0
 */
class SessionUtil
{
    /**
     * Get a session variable
     *
     * @param string  $name                 Name of the session variable to get
     * @param string  $default              The default value to return if the requested session variable is not set
     * @param string  $path                 Path
     * @param boolean $autocreate           Whether or not to autocreate the supplied path (optional) (default=true)
     * @param boolean $overwriteExistingVar Whether or not to overwrite existing/set variable entries which the given path requires to be arrays (optional) (default=false)
     *
     * @return string Session variable requested
     */
    public static function getVar($name, $default = false, $path = '/', $autocreate = true, $overwriteExistingVar = false)
    {
        $session = ServiceUtil::getManager()->get('session');

        return $session->get($name, $default, $path);
    }

    /**
     * Set a session variable.
     *
     * @param string  $name                 Name of the session variable to set
     * @param string  $value                Value to set the named session variable
     * @param string  $path                 Path to traverse to reach the element we wish to return (optional) (default='/')
     * @param boolean $autocreate           Whether or not to autocreate the supplied path (optional) (default=true)
     * @param boolean $overwriteExistingVar Whether or not to overwrite existing/set variable entries which the given path requires to be arrays (optional) (default=false)
     *
     * @return boolean true upon success, false upon failure
     */
    public static function setVar($name, $value, $path = '/', $autocreate = true, $overwriteExistingVar = false)
    {
        $session = ServiceUtil::getManager()->get('session');

        if ($name == 'uid') {
            $session->regenerate(true);
        }

        return $session->set($name, $value, $path);
    }

    /**
     * Delete a session variable.
     *
     * @param string $name    Name of the session variable to delete
     * @param mixed  $default The default value to return if the requested session variable is not set
     * @param string $path    Path to traverse to reach the element we wish to return (optional) (default='/')
     *
     * @return mixed The value of the session variable being deleted, or the value provided in $default if the variable is not set
     */
    public static function delVar($name, $default = false, $path = '/')
    {
        $session = ServiceUtil::getManager()->get('session');
        $value = $session->get($name, $default, $path);
        $session->del($name, $path);

        return $value;
    }

    /**
     * Session required.
     *
     * Starts a session or terminates loading.
     *
     * @return void
     */
    public static function requireSession()
    {
        EventUtil::getManager()->dispatch('session.require', new \Zikula\Core\Event\GenericEvent());
    }

    /**
     * Let session expire nicely
     *
     * @return void
     */
    public static function expire()
    {
        if (self::getVar('uid') == '0') {
            // no need to display expiry for anon users with sessions since it's invisible anyway
            // handle expired sessions differently
            self::regenerate(true);

            return;
        }

        $session = ServiceUtil::getManager()->get('session');
        $session->invalidate();
        self::setVar('session_expire', true);
    }

    /**
     * Check if a session has expired or not
     *
     * @return boolean
     */
    public static function hasExpired()
    {
        return self::getVar('session_expired', false);
    }

    /**
     * Regerate session id.
     *
     * @param boolean $force Force regeneration, default: false
     *
     * @return void
     */
    public static function regenerate($force = false)
    {
        $session = ServiceUtil::getManager()->get('session');
        $session->migrate($force);

        return;
    }

    /**
     * Define the name of our session cookie.
     *
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

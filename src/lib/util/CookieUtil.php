<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * CookieUtil
 */
class CookieUtil
{
    /**
     * Set a cookie value
     *
     * @author Drak
     * @param string $name name of cookie
     * @param string $value
     * @param int $expires unix epoch dat for expiry
     * @param string $path
     * @param string $domain domain must be at least .domain.tld
     * @param bool $secure to set if cookie must only be set over existing https connection
     * @param bool $signed override system setting to use signatures
     * @return bool
     */
    public static function setCookie($name, $value='', $expires=null, $path=null, $domain=null, $secure=null, $signed = true)
    {
        if (!$name) {
            return z_exit(__f("Error! In 'setCookie', you must specify at least the cookie name '%s'.", DataUtil::formatForDisplay($name)));
        }

        if (!is_string($value)) {
            return z_exit('setCookie: ' . DataUtil::formatForDisplay($value) . ' must be a string');
        }

        if (System::getVar('signcookies') && (!$signed==false)){ // sign the cookie
            $value = SecurityUtil::signData($value);
        }

        return setcookie($name, $value, $expires, $path, $domain, $secure);
    }

    /**
     * Get a cookie
     *
     * @author Drak
     * @param string $name name of cookie
     * @param bool $signed override system setting to use signatures
     * @param bool $default default value
     * @return mixed cookie value as string or bool false
     */
    public static function getCookie($name, $signed=true, $default='')
    {
        $cookie = FormUtil::getPassedValue($name, $default, 'COOKIE');
        if (System::getVar('signcookies') && (!$signed==false)){
            return SecurityUtil::checkSignedData($cookie);
        }

        return $cookie;
    }

    /**
     * Delete given cookie
     * Can be called multiple times, but must be called before any output
     * is sent to browser or it wont work.
     *
     * @param string $name Name of cookie.
     *
     * @return bool
     */
    public static function deleteCookie($name)
    {
        return self::setCookie($name, '', time());
    }
}

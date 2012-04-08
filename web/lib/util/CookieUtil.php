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
 * CookieUtil.
 */
class CookieUtil
{
    /**
     * Set a cookie value.
     *
     * @param string  $name    Name of cookie.
     * @param string  $value   Value.
     * @param integer $expires Unix epoch date for expiry.
     * @param string  $path    Cookie path.
     * @param string  $domain  Domain must be at least .domain.tld.
     * @param boolean $secure  To set if cookie must only be set over existing https connection.
     * @param boolean $signed  Override system setting to use signatures.
     *
     * @return boolean
     */
    public static function setCookie($name, $value='', $expires=null, $path=null, $domain=null, $secure=null, $signed = true)
    {
        if (!$name) {
            return z_exit(__f("Error! In 'setCookie', you must specify at least the cookie name '%s'.", DataUtil::formatForDisplay($name)));
        }

        if (!is_string($value)) {
            return z_exit('setCookie: ' . DataUtil::formatForDisplay($value) . ' must be a string');
        }

        if (System::getVar('signcookies') && (!$signed == false)) {
            // sign the cookie
            $value = SecurityUtil::signData($value);
        }

        return setcookie($name, $value, $expires, $path, $domain, $secure);
    }

    /**
     * Get a cookie.
     *
     * @param string  $name    Name of cookie.
     * @param boolean $signed  Override system setting to use signatures.
     * @param boolean $default Default value.
     *
     * @return mixed Cookie value as string or bool false.
     */
    public static function getCookie($name, $signed=true, $default='')
    {
        $cookie = FormUtil::getPassedValue($name, $default, 'COOKIE');
        if (System::getVar('signcookies') && (!$signed == false)) {
            return SecurityUtil::checkSignedData($cookie);
        }

        return $cookie;
    }

    /**
     * Delete given cookie.
     *
     * Can be called multiple times, but must be called before any output
     * is sent to browser or it wont work.
     *
     * @param string $name Name of cookie.
     *
     * @return boolean
     */
    public static function deleteCookie($name)
    {
        return self::setCookie($name, '', time());
    }

}

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
 * CookieUtil.
 * @deprecated remove at Core-2.0
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
    public static function setCookie($name, $value = '', $expires = null, $path = null, $domain = null, $secure = null, $signed = true)
    {
        if (!$name) {
            throw new \Exception(__f("Error! In 'setCookie', you must specify at least the cookie name '%s'.", DataUtil::formatForDisplay($name)));
        }

        if (!is_string($value)) {
            throw new \Exception('setCookie: ' . DataUtil::formatForDisplay($value) . ' must be a string');
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
    public static function getCookie($name, $signed = true, $default = '')
    {
        $request = \ServiceUtil::get('request');

        if (!$request->cookies->has($name)) {
            return $default;
        }

        $cookie = $request->cookies->get($name);
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

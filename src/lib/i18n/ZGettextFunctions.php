<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

if(!defined('LC_MESSAGES')) {
    define('LC_MESSAGES', 5);
}

/**
 * Format _dgettext string.
 *
 * uses sprintf() formatting %s etc, and positional %1$s, %2$s etc.
 * @link http://us.php.net/manual/en/function.sprintf.php
 * %1$s specifies the first occurance in the array of params, %2$s the second
 *
 * Note params must passed either as
 * __('beer') or as $beer where $beer = __('beer') somewhere before the call
 * __f('I want some %s with my meal', __('beer'));
 * __f('Give me %s with my %s', array(__('some sausages'), __('beer'));
 * __f('%1$s buy me %2$s', array('Drak', __('a beer'));
 *
 */
function __f($msgid, $params, $domain=null)
{
    $msgstr = (isset($domain) ? _dgettext($domain, $msgid) : _gettext($msgid));
    $params = (is_array($params) ? $params : array($params));
    return vsprintf($msgstr, $params);
}

/**
 * Format _dngettext string.
 *
 * uses sprintf() formatting %s etc, and positional %1$s, %2$s etc.
 * @link http://us.php.net/manual/en/function.sprintf.php
 * %1$s specifies the first occurance in the array of params, %2$s the second
 *
 * Note params must passed either as
 * __('now') or as $value where $value = __('now') somewhere before the call
 * _fn('apple %s', 'apples %s', __('now'), 4);
 * _fn('apple %s', 'apples %s', $value, 4);
 *
 */
function _fn($sin, $plu, $n, $params, $domain=null)
{
    $msgstr = (isset($domain) ? _dngettext($domain, $sin, $plu, (int)$n) : _ngettext($sin, $plu, (int)$n));
    $params = (is_array($params) ? $params : array($params));
    return vsprintf($msgstr, $params);
}

/**
 * Alias for gettext.
 */
function __($msgid, $domain=null)
{
    return (isset($domain) ? _dgettext($domain, $msgid) : _gettext($msgid));
}

/**
 * Plural translation
 *
 * @param string singular  $singular
 * @param string plural $plural
 * @param int count $count
 * @param string domain $domain
 * @return string
 */
function _n($singular, $plural, $count, $domain=null)
{
    return (isset($domain) ? _dngettext($domain, $singular, $plural, (int)$count) : _ngettext($singular, $plural, (int)$count));
}

/**
 * No operation gettext
 *
 * @param string $msgid
 * @return string
 */
function no__($msgid)
{
    return $msgid;
}

/**
 * Lookup a message in the current domain.
 */
function _gettext($msgid)
{
    return ZGettext::getReader()->translate($msgid);
}

/**
 * Plural version of gettext.
 */
function _ngettext($single, $plural, $number)
{
    return ZGettext::getReader()->ngettext($single, $plural, $number);
}

/**
 * Override the current domain.
 */
function _dgettext($domain, $msgid)
{
    return ZGettext::getReader($domain)->translate($msgid);
}
/**
 * Plural version of dgettext.
 */
function _dngettext($domain, $single, $plural, $number)
{
    return ZGettext::getReader($domain)->ngettext($single, $plural, $number);
}

/**
 * Overrides the domain and category for a single lookup.
 */
function _dcgettext($domain, $msgid, $category)
{
    return ZGettext::getReader($domain, $category)->translate($msgid);
}

/**
 * Plural version of dcgettext.
 */
function _dcngettext($domain, $single, $plural, $number, $category)
{
    return ZGettext::getReader($domain, $category)->ngettext($single, $plural, $number);
}

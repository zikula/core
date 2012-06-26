<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

if (!defined('LC_MESSAGES')) {
    define('LC_MESSAGES', 5);
}

/**
 * Translates a message.
 *
 * @param string $message Message
 * @param string $domain  Domain
 *
 * @return string
 */
function __($message, $domain=null)
{
    return isset($domain) ? _dgettext($domain, $message) : _gettext($message);
}

/**
 * Translates message by context.
 *
 * @param string $message Message
 * @param string $context Context
 * @param string $domain  Domain
 *
 * @return string
 */
function __p($context, $message, $domain=null)
{
    return isset($domain) ? _dpgettext($domain, $context, $message) : _pgettext($context, $message);
}

/**
 * Translate plural message.
 *
 * @param string  $singular Singular
 * @param string  $plural   Plural
 * @param integer $count    Count
 * @param string  $domain   Gettext domain
 *
 * @return string
 */
function _n($singular, $plural, $count, $domain=null)
{
    return isset($domain) ? _dngettext($domain, $singular, $plural, $count) : _ngettext($singular, $plural, $count);
}

/**
 * Context based plural translation.
 *
 * @param string  $context  Context.
 * @param string  $singular Singular form
 * @param string  $plural   Plural form
 * @param integer $count    Count
 * @param string  $domain   Domain
 *
 * @return string
 */
function _np($context, $singular, $plural, $count, $domain=null)
{
    return isset($domain) ? _dnpgettext($domain, $context, $singular, $plural, $count) : _npgettext($context, $singular, $plural, $count);
}

/**
 * Translate message with string replacements.
 *
 * Uses sprintf() formatting %s etc, and positional %1$s, %2$s etc.
 * @link http://php.net/sprintf
 * %1$s specifies the first occurrence in the array of params, %2$s the second
 *
 * Note params must passed either as
 * __('beer') or as $beer where $beer = __('beer') somewhere before the call
 * __f('I want some %s with my meal', __('beer'));
 * __f('Give me %s with my %s', array(__('some sausages'), __('beer'));
 * __f('%1$s buy me %2$s', array('Drak', __('a beer'));
 *
 * @param string $message The message
 * @param mixed  $params  Format parameters or array of parameters
 * @param string $domain  Domain
 *
 * @return string
 */
function __f($message, $params, $domain=null)
{
    $msgstr = (isset($domain) ? _dgettext($domain, $message) : _gettext($message));
    $params = (is_array($params) ? $params : array($params));

    return vsprintf($msgstr, $params);
}

/**
 * Translate message using string replacements by context.
 *
 * Uses sprintf() formatting %s etc, and positional %1$s, %2$s etc.
 * @link http://php.net/sprintf
 * %1$s specifies the first occurrence in the array of params, %2$s the second
 *
 * Note params must passed either as
 * __p('beer') or as $beer where $beer = __p('beer') somewhere before the call
 * __pf('I want some %s with my meal', __p('beer'));
 * __pf('Give me %s with my %s', array(__p('some sausages'), __p('beer'));
 * __pf('%1$s buy me %2$s', array('Drak', __p('a beer'));
 *
 * @param string $context Message context
 * @param string $message The message
 * @param mixed  $params  Format parameters or array of parameters
 * @param string $domain  Domain
 *
 * @return string
 */
function __fp($context, $message, $params, $domain=null)
{
    $msgstr = isset($domain) ? _dpgettext($domain, $context, $message) : _pgettext($context, $message);
    $params = is_array($params) ? $params : array($params);

    return vsprintf($msgstr, $params);
}

/**
 * Translate plural message.
 *
 * Uses sprintf() formatting %s etc, and positional %1$s, %2$s etc.
 * {@link: http://php.net/sprintf}
 * %1$s specifies the first occurance in the array of params, %2$s the second
 *
 * Note params must passed either as
 * __('now') or as $value where $value = __('now') somewhere before the call
 * _fn('apple %s', 'apples %s', __('now'), 4);
 * _fn('apple %s', 'apples %s', $value, 4);
 *
 * @param string  $singular Singular form
 * @param string  $plural   Plural form
 * @param integer $n        Count
 * @param mixed   $params   Format parameters or array of parameters
 * @param string  $domain   Domain
 *
 * @return string
 */
function _fn($singular, $plural, $n, $params, $domain=null)
{
    $msgstr = isset($domain) ? _dngettext($domain, $singular, $plural, $n) : _ngettext($singular, $plural, $n);
    $params = is_array($params) ? $params : array($params);

    return vsprintf($msgstr, $params);
}

/**
 * Translate plural message with string replacements by context.
 *
 * Uses sprintf() formatting %s etc, and positional %1$s, %2$s etc.
 * {@link: http://php.net/sprintf}
 * %1$s specifies the first occurance in the array of params, %2$s the second
 *
 * Note params must passed either as
 * __('now') or as $value where $value = __('now') somewhere before the call
 * _fn('apple %s', 'apples %s', __('now'), 4);
 * _fn('apple %s', 'apples %s', $value, 4);
 *
 * @param string  $context  Message context
 * @param string  $singular Singular form
 * @param string  $plural   Plural form
 * @param integer $n        Count
 * @param mixed   $params   Format parameters or array of parameters
 * @param string  $domain   Domain
 *
 * @return string
 */
function _fnp($context, $singular, $plural, $n, $params, $domain=null)
{
    $msgstr = isset($domain) ? _dnpgettext($domain, $context, $singular, $plural, $n) : _npgettext($context, $singular, $plural, $n);
    $params = is_array($params) ? $params : array($params);

    return vsprintf($msgstr, $params);
}

/**
 * No operation gettext.
 *
 * @param string $message The Message.
 *
 * @return string
 */
function no__($message)
{
    return $message;
}

/**
 * Lookup a message in the current domain.
 *
 * @param string $message Message
 *
 * @return string
 */
function _gettext($message)
{
    return ZGettext::getReader()->translate($message);
}

/**
 * Plural version of gettext.
 *
 * @param string  $singular Singular form
 * @param string  $plural   Plural form
 * @param integer $number   Count
 *
 * @return string
 */
function _ngettext($singular, $plural, $number)
{
    return ZGettext::getReader()->ngettext($singular, $plural, $number);
}

/**
 * Override the current domain.
 *
 * @param string $domain  Domain
 * @param string $message Message
 *
 * @return string
 */
function _dgettext($domain, $message)
{
    return ZGettext::getReader($domain)->translate($message);
}
/**
 * Plural version of dgettext.
 *
 * @param string  $domain   Domain
 * @param string  $singular Singular
 * @param string  $plural   Plural
 * @param integer $number   Count
 *
 * @return string
 */
function _dngettext($domain, $singular, $plural, $number)
{
    return ZGettext::getReader($domain)->ngettext($singular, $plural, $number);
}

/**
 * Overrides the domain and category for a single lookup.
 *
 * @param string  $domain   Domain
 * @param string  $message  Message
 * @param integer $category LC constant
 *
 * @return string
 */
function _dcgettext($domain, $message, $category)
{
    return ZGettext::getReader($domain, $category)->translate($message);
}

/**
 * Plural version of dcgettext.
 *
 * @param string  $domain   Domain
 * @param string  $singular Singular
 * @param string  $plural   Plural
 * @param integer $number   Count
 * @param integer $category LC_CONSTANT
 *
 * @return string
 */
function _dcngettext($domain, $singular, $plural, $number, $category)
{
    return ZGettext::getReader($domain, $category)->ngettext($singular, $plural, $number);
}

/**
 * Translate string by context using default domain.
 *
 * @param string $context
 * @param string $message
 *
 * @return string
 */
function _pgettext($context, $message)
{
    $contextMsg = $context.'\004'.$message;
    $translation = _gettext($message);

    return $translation == $contextMsg ? $message : $translation;
}

/**
 * Translate string by context and domain.
 *
 * @param string $domain  Domain
 * @param string $context Context
 * @param string $message Message
 *
 * @return string
 */
function _dpgettext($domain, $context, $message)
{
    $contextMsg = $context.'\004'.$message;
    $translation = _dgettext($domain, $message);

    return $translation == $contextMsg ? $message : $translation;
}

/**
 * Translate message by domain, context and LC category.
 *
 * @param string  $domain   Domain
 * @param string  $context  Context
 * @param string  $message  Message
 * @param integer $category LC category
 *
 * @return string
 */
function _dcpgettext($domain, $context, $message, $category)
{
    $contextMsg = $context.'\004'.$message;
    $translation = _dcgettext($domain, $message, $category);

    return $translation == $contextMsg ? $message : $translation;
}

/**
 * Translate plural message by context and default domain.
 *
 * @param string  $context  Context
 * @param string  $singular Singular form
 * @param string  $plural   Plural form
 * @param integer $count    Count
 *
 * @return string
 */
function _npgettext($context, $singular, $plural, $count)
{
    $singular = $context.'\004'.$singular;
    $plural = $context.'\004'.$plural;
    $translation = _ngettext($singular, $plural, $count);
    if ($translation == $singular) {
        return $singular;
    }

    if ($translation == $plural) {
        return $plural;
    }

    return $translation;
}

/**
 * Translate plural message by domain and context.
 *
 * @param string  $domain   Domain
 * @param string  $context  Context
 * @param string  $singular Singular form
 * @param string  $plural   Plural form
 * @param integer $count    Count
 *
 * @return string
 */
function _dnpgettext($domain, $context, $singular, $plural, $count)
{
    $singular = $context.'\004'.$singular;
    $plural = $context.'\004'.$plural;
    $translation = _dngettext($domain, $singular, $plural, $count);
    if ($translation == $singular) {
        return $singular;
    }

    if ($translation == $plural) {
        return $plural;
    }

    return $translation;
}

/**
 * Translate plural message by domain, context and LC category.
 *
 * @param string  $domain   Domain
 * @param string  $context  Context
 * @param string  $singular Singular form
 * @param string  $plural   Plural form
 * @param integer $count    Count
 * @param integer $category Category
 *
 * @return string
 */
function _dcnpgettext($domain, $context, $singular, $plural, $count, $category)
{
    $singular = $context.'\004'.$singular;
    $plural = $context.'\004'.$plural;
    $translation = _dcngettext($domain, $singular, $plural, $count, $category);
    if ($translation == $singular) {
        return $singular;
    }

    if ($translation == $plural) {
        return $plural;
    }

    return $translation;
}


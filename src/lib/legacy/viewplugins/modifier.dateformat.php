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
 * Dateformat modifier
 * Include the shared.make_timestamp.php plugin
 *
 * Example:
 * {$timestamp|dateformat:'%a, %d %b %Y'}
 * Saturday, 12 Dec 2009
 *
 * {$timestamp|dateformat:'%a, %d %b %Y':$defaultimestamp}
 * If $timestamp is empty the $defaultimestamp will be used
 */
require_once $smarty->_get_plugin_filepath('shared', 'make_timestamp');

/**
 * Zikula_View modifier to format datestamps via strftime according to locale setting in Zikula.
 *
 * @param string $string       Input date string
 * @param string $format       Strftime format for output
 * @param string $default_date Default date if $string is empty
 *
 * @uses \smarty_make_timestamp()
 *
 * @return string The modified output
 */
function smarty_modifier_dateformat($string, $format = 'datebrief', $default_date = null)
{
    if (empty($format)) {
        $format = 'datebrief';
    }

    if (!empty($string)) {
        return DateUtil::formatDatetime($string, $format);
    } elseif (!empty($default_date)) {
        return DateUtil::formatDatetime($default_date, $format);
    }

    return '';
}

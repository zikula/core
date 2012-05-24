<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
 * @param string $string       Input date string.
 * @param string $format       Strftime format for output.
 * @param string $default_date Default date if $string is empty.
 *
 * @uses smarty_make_timestamp()
 *
 * @return string The modified output.
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

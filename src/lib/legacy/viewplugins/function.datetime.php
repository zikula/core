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
 * Zikula_View function to display the current date and time
 *
 * Example
 * {datetime}
 *
 * {datetime format='datebrief'}
 *
 * {datetime format='%b %d, %Y - %I:%M %p'}
 *
 * Format:
 * %a - abbreviated weekday name according to the current locale
 * %A = full weekday name according to the current locale
 * %b = abbreviated month name according to the current locale
 * %B = full month name according to the current locale
 * %d = day of the month as a decimal number (range 01 to 31)
 * %D = same as %m/%d/%y
 * %y = year as a decimal number without a century (range 00 to 99)
 * %Y = year as a decimal number including the century
 * %H = hour as a decimal number using a 24-hour clock (range 00 to 23)
 * %I = hour as a decimal number using a 12-hour clock (range 01 to 12)
 * %M = minute as a decimal number
 * %S = second as a decimal number
 * %p = either 'am' or 'pm' according to the given time value, or the corresponding strings for the current locale
 *
 * http://www.php.net/manual/en/function.strftime.php
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @see    function.datetime.php::smarty_function_datetime()
 *
 * @return string
 */
function smarty_function_datetime($params, Zikula_View $view)
{
    // set some defaults
    $format = isset($params['format']) ? $params['format'] : __('%b %d, %Y - %I:%M %p');

    if (strpos($format, '%') !== false) {
        // allow the use of conversion specifiers
        return DateUtil::formatDatetime('', $format);
    }

    return DateUtil::formatDatetime('', $format);
}

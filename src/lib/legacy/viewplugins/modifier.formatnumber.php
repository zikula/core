<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Format number.
 *
 * Example:
 *   {$myVar|formatnumber}
 *
 * @param string $string         The contents to transform.
 * @param mixed  $decimal_points Desc : null=default locale, false=precision, int=precision.
 *
 * @return string The modified output.
 */
function smarty_modifier_formatNumber($string, $decimal_points = null)
{
    return DataUtil::formatNumber($string, $decimal_points);
}

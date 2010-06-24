<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Format number.
 * 
 * Example:
 *   {$MyVar|formatnumber}
 *
 * @param string $string         The contents to transform.
 * @param mixed  $decimal_points Desc : null=default locale, false=precision, int=precision.
 * 
 * @return string The modified output.
 */
function smarty_modifier_formatNumber($string, $decimal_points=null)
{
    return DataUtil::formatNumber($string, $decimal_points);
}

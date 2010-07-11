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
 * Zikula_View modifier to prepare a variable for display by converting all applicable characters to HTML entities.
 *
 * Example
 *
 *   {$MyVar|htmlentities}
 *
 * @param mixed $string The contents to transform.
 *
 * @return string The modified output.
 */
function smarty_modifier_htmlentities($string, $quote_style=ENT_NOQUOTES, $charset='UTF-8', $double_encode=false)
{
    return htmlentities($string, $quote_style, $charset, $double_encode);
}

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
 * Smarty modifier to prepare a variable for display
 * by converting special characters to HTML entities
 *
 * Example
 *
 *   {$MyVar|htmlspecialchars}
 *
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_htmlspecialchars($string)
{
    return htmlspecialchars($string);
}

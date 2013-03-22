<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
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
 * Zikula_View modifier translate html input newlines to <br /> sequences.
 *
 * Example
 *
 *   {$myvar|nl2html}
 *
 * @param string $string The string to operate on.
 *
 * @return string The converted string.
 */
function smarty_modifier_nl2html($string)
{
    return StringUtil::nl2html($string);
}

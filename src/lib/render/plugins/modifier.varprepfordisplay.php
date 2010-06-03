<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty modifier to prepare a variable for display
 *
 * This modifier carries out suitable escaping of characters such that when
 * output as part of an HTML page the exact string is displayed.
 *
 * Running this modifier multiple times is cumulative and is not reversible.
 * It recommended that variables that have been returned from this modifier
 * are only used to display the results, and then discarded.
 *
 * Example
 *
 *   <!--[$MyVar|DataUtil::formatForDisplay]-->
 *
 * @see          modifier.DataUtil::formatForDisplayHTML.php::smarty_modifier_DataUtil::formatForDisplayHTML()
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_varprepfordisplay ($string)
{
    return DataUtil::formatForDisplay($string);
}

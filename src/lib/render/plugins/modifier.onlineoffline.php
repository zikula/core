<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty modifier to turn a boolean value into a suitable language string
 *
 * Example
 *
 *   {$myvar|onlineoffline|varprepfordisplay} returns Online if $myvar = 1 and Offline if $myvar = 0
 *
 * @param        string    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_onlineoffline($string)
{
    if ($string != '0' && $string != '1') return $string;

    if ((bool)$string) {
        return __('on-line');
    } else {
        return __('off-line');
    }
}

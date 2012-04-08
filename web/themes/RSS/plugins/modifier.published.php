<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty modifier format an issue date for an atom news feed
 *
 * Example
 *
 *   {$MyVar|published}
 *
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_published($string)
{
    return date('D, d M Y H:i:s O', strtotime($string));
}

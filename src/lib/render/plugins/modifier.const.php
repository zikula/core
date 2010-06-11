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
 * Smarty modifier to convert string to PHP constant (required to support
 * class constants
 *
 * Example
 *
 *   {'ModUtil::TYPE_MODULE'|const}
 *
 * @see          modifier.varprepfordisplay.php::smarty_modifier_varprepfordisplay()
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_const($string)
{
    return constant($string);
}

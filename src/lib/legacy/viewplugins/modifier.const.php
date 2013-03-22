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
 * Zikula_View modifier to convert string to PHP constant (required to support class constants).
 *
 * Example
 *
 *   {'ModUtil::TYPE_MODULE'|const}
 *
 * @param string $string The contents to transform.
 *
 * @see    modifier.safetext.php::smarty_modifier_safetext
 *
 * @return string The modified output.
 */
function smarty_modifier_const($string)
{
    return constant($string);
}

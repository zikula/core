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
 * Zikula_View modifier to retrieve a language name from its l2 code
 *
 * Example:
 *   {$language|getlanguagename}
 *
 * @param string $langcode The language to process.
 *
 * @return string The language name.
 */
function smarty_modifier_getlanguagename($langcode)
{
    return ZLanguage::getLanguageName($langcode);
}

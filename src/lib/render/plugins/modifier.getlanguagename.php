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
 * Smarty modifier to retrieve a language name from its l2 code
 *
 * Example
 *
 *   {$language|getlanguagename}
 *
 * @param        string   $langcode   the language to process
 * @return       string   the language name
 */
function smarty_modifier_getlanguagename($langcode)
{
    return ZLanguage::getLanguageName($langcode);
}
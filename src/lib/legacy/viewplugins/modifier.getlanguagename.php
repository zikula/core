<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

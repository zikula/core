<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_View modifier translate html input newlines to <br /> sequences.
 *
 * Example
 *
 *   {$myVar|nl2html}
 *
 * @param string $string The string to operate on.
 *
 * @return string The converted string.
 */
function smarty_modifier_nl2html($string)
{
    return StringUtil::nl2html($string);
}

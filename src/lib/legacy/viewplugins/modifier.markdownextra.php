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
 * Zikula_View modifier parse markdown
 *
 * Example
 *
 *   {$myVar|markdownextra}
 *
 * @param string $string The contents to transform.
 *
 * @return string The modified output.
 */
function smarty_modifier_markdownextra($string)
{
    return StringUtil::getMarkdownExtraParser()->transform($string);
}

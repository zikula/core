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
 * Zikula_View modifier to prepare a variable for display by converting special characters to HTML entities.
 *
 * Example
 *
 *   {$myVar|htmlspecialchars}
 *
 * @param mixed $string The contents to transform.
 *
 * @return string The modified output.
 */
function smarty_modifier_htmlspecialchars($string)
{
    return htmlspecialchars($string);
}

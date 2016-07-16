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
 * Zikula_View modifier to prepare a variable for display by converting all applicable characters to HTML entities.
 *
 * Example
 *
 *   {$myVar|htmlentities}
 *
 * @param mixed $string        The contents to transform
 * @param mixed $quote_style   Constant to define what will be done with 'single' and "double" quotes
 * @param mixed $charset       Character set to use in conversion
 * @param mixed $double_encode Encode or not existing html entities
 *
 * @return string The modified output
 */
function smarty_modifier_htmlentities($string, $quote_style = ENT_NOQUOTES, $charset = 'UTF-8', $double_encode = false)
{
    return htmlentities($string, $quote_style, $charset, $double_encode);
}

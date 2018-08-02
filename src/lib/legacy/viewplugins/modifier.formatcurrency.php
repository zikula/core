<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Format currency.
 *
 * Example:
 *   {$myVar|formatcurrency}
 *
 * @param string $string The contents to transform
 *
 * @return string The modified output
 */
function smarty_modifier_formatCurrency($string)
{
    return DataUtil::formatCurrency($string);
}
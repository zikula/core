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
 * Zikula_View modifier to turn a boolean value into a suitable language string
 *
 * Example
 *
 *   {$myVar|activeinactive|safetext} returns Active if $myVar = 1 and Inactive if $myVar = 0
 *
 * @param string $string The contents to transform.
 *
 * @return string The modified output.
 */
function smarty_modifier_activeinactive($string)
{
    if ($string != '0' && $string != '1') {
        return $string;
    }

    if ((bool)$string) {
        return __('Active');
    } else {
        return __('Inactive');
    }
}

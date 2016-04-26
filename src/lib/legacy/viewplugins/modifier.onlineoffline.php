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
 * Zikula_View modifier to turn a boolean value into a suitable language string
 *
 * Example
 *
 *   {$myVar|onlineoffline|safetext} returns Online if $myVar = 1 and Offline if $myVar = 0
 *
 * @param mixed $string The contents to transform.
 *
 * @return string The modified output.
 */
function smarty_modifier_onlineoffline($string)
{
    if ($string != '0' && $string != '1') {
        return $string;
    }

    if ((bool)$string) {
        return __('on-line');
    } else {
        return __('off-line');
    }
}

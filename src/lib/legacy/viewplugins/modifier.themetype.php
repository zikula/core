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
 * Zikula_View modifier to convert theme type into a language string
 *
 * Example
 *
 *   {$mythemetype|themetype}
 *
 * @param array $string The contents to transform
 *
 * @see    modifier.safetext.php::smarty_modifier_safetext
 *
 * @return string The modified output
 */
function smarty_modifier_themetype($string)
{
    switch ((int)$string) {
        case 3:
            return __('Theme 3.0');

    }
}

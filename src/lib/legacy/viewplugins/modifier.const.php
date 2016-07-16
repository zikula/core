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
 * Zikula_View modifier to convert string to PHP constant (required to support class constants).
 *
 * Example
 *
 *   {'ModUtil::TYPE_MODULE'|const}
 *
 * @param string $string The contents to transform
 *
 * @see    modifier.safetext.php::smarty_modifier_safetext
 *
 * @return string The modified output
 */
function smarty_modifier_const($string)
{
    return constant($string);
}

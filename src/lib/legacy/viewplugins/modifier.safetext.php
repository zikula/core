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
 * Zikula_View modifier to prepare a variable for display
 *
 * This modifier carries out suitable escaping of characters such that when
 * output as part of an HTML page the exact string is displayed.
 *
 * Running this modifier multiple times is cumulative and is not reversible.
 * It recommended that variables that have been returned from this modifier
 * are only used to display the results, and then discarded.
 *
 * Example
 *
 *   {$myVar|safetext}
 *
 * @param mixed $string The contents to transform.
 *
 * @see    modifier.safetext.php::smarty_modifier_DataUtil::formatForDisplayHTML()
 *
 * @return string The modified output.
 */
function smarty_modifier_safetext($string)
{
    return DataUtil::formatForDisplay($string);
}

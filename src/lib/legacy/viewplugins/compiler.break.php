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
 * Zikula_View function provide {break} in templates.
 *
 * @param string          $content  The content.
 * @param Smarty_Compiler $compiler Compiler object.
 *
 * @return string 'break;'
 */
function smarty_compiler_break($content, Smarty_Compiler $compiler)
{
    return 'break;';
}

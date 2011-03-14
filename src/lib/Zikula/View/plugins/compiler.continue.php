<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View function provide {continue} in templates.
 *
 * @param string          $content  The content.
 * @param Smarty_Compiler $compiler Compiler object.
 *
 * @return string 'continue;'
 */
function smarty_compiler_continue($content, Smarty_Compiler $compiler)
{
    return 'continue;';
}

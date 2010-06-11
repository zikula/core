<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty modifier to apply transform hooks
 *
 * This modifier will run the transform hooks that are enabled for the
 * corresponding module (like Autolinks, bbclick and others).
 *
 * Available parameters:
 *   - modname:  The well-known name of the calling module; passed to the hook function
 *               in the extrainfo array
 * Example
 *
 *   {$MyVar|modcallhooks}
 *
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_modcallhooks($string, $modname = '')
{
    $extrainfo = array($string);
    if (!empty($modname)) {
        $extrainfo['module'] = $modname;
    }

    list($string) = ModUtil::callHooks('item', 'transform', '', $extrainfo);
    return $string;
}

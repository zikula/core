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
 * Zikula_View modifier to apply transform hooks
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
 * @param mixed  $string  The contents to transform.
 * @param string $modname Module name.
 *
 * @return string The modified output.
 */
function smarty_modifier_modcallhooks($string, $modname = '')
{
    LogUtil::log(__f('Warning! Template modifier {$var|%1$s} is deprecated.', array('modcallhooks')), E_USER_DEPRECATED);

    $extrainfo = array($string);
    if (!empty($modname)) {
        $extrainfo['module'] = $modname;
    }

    list($string) = ModUtil::callHooks('item', 'transform', '', $extrainfo);

    return $string;
}
